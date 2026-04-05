<?php

use CodeIgniter\Database\BaseConnection;

class Server_model extends CI_Model
{
    private Realm $realm;
    private int $realmId = 1;
    private BaseConnection $characters;
    private BaseConnection $account;

    public function __construct()
    {
        parent::__construct();

        $this->load->config('server/server');
    }

    public function setRealm(int $realmId): void
    {
        $this->realmId = $realmId;
        $this->realm = $this->realms->getRealm($realmId);
    }

    public function getRealmCards(): array
    {
        $cacheKey = 'server_realm_cards';
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $cards = [];

        foreach ($this->realms->getRealms() as $realm) {
            $cards[] = [
                'id' => $realm->getId(),
                'name' => $realm->getName(),
                'expansion' => $realm->getExpansionName(),
                'online' => $realm->isOnline(true),
                'players' => $realm->isOnline(true) ? $realm->getOnline() : 0,
                'cap' => $realm->getCap(),
                'percentage' => $realm->getPercentage(),
                'uptime' => $realm->isOnline(true) ? $this->getRealmUptime($realm->getId()) : 'Offline',
            ];
        }

        $this->cache->save($cacheKey, $cards, (int) $this->config->item('cache_seconds'));

        return $cards;
    }

    public function getOnlineSpotlight(int $limit): array
    {
        $cacheKey = 'server_online_spotlight_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        if (!$this->realm->isOnline()) {
            return [];
        }

        $players = $this->realm->getCharacters()->getOnlinePlayers(false);

        if (!$players) {
            return [];
        }

        usort($players, static function ($left, $right) {
            if ($left['level'] === $right['level']) {
                return strcmp($left['name'], $right['name']);
            }

            return $right['level'] <=> $left['level'];
        });

        $players = array_slice($players, 0, $limit);

        $spotlight = array_map(function ($character) {
            return [
                'guid' => $character['guid'],
                'name' => $character['name'],
                'level' => $character['level'],
                'class' => $this->realms->getClass((int) $character['class']),
                'race' => $this->realms->getRace((int) $character['race']),
                'zone' => $this->realms->getZone((int) $character['zone']),
            ];
        }, $players);

        $this->cache->save($cacheKey, $spotlight, (int) $this->config->item('cache_seconds'));

        return $spotlight;
    }

    public function getActiveZones(int $limit): array
    {
        $cacheKey = 'server_active_zones_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        if (!$this->realm->isOnline()) {
            return [];
        }

        $players = $this->realm->getCharacters()->getOnlinePlayers(false);

        if (!$players) {
            return [];
        }

        $zones = [];

        foreach ($players as $character) {
            $zoneId = (int) $character['zone'];

            if (!array_key_exists($zoneId, $zones)) {
                $zones[$zoneId] = [
                    'zone' => $this->realms->getZone($zoneId),
                    'count' => 0,
                    'names' => [],
                ];
            }

            $zones[$zoneId]['count']++;

            if (count($zones[$zoneId]['names']) < 3) {
                $zones[$zoneId]['names'][] = $character['name'];
            }
        }

        usort($zones, static function ($left, $right) {
            return $right['count'] <=> $left['count'];
        });

        $zones = array_slice($zones, 0, $limit);

        $this->cache->save($cacheKey, $zones, (int) $this->config->item('cache_seconds'));

        return $zones;
    }

    public function getRecentAchievements(int $limit): array
    {
        $cacheKey = 'server_recent_achievements_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        $worldDatabase = $this->realm->getConfig('world_database');

        try {
            $query = $this->characters->query(
                "SELECT ca.guid, ca.achievement, ca.date, c.name, c.level, c.class, c.race,
                        COALESCE(NULLIF(ad.Title_Lang_enUS, ''), CONCAT('Achievement #', ca.achievement)) AS title
                 FROM character_achievement ca
                 INNER JOIN " . table('characters', $this->realmId) . " c
                    ON c." . column('characters', 'guid', false, $this->realmId) . " = ca.guid
                 LEFT JOIN `" . $worldDatabase . "`.achievement_dbc ad
                    ON ad.ID = ca.achievement
                 ORDER BY ca.date DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (Throwable) {
            return [];
        }

        if (!$query || !$query->getNumRows()) {
            return [];
        }

        $items = array_map(function ($row) {
            return [
                'guid' => $row['guid'],
                'name' => $row['name'],
                'level' => $row['level'],
                'class' => $this->realms->getClass((int) $row['class']),
                'race' => $this->realms->getRace((int) $row['race']),
                'title' => $row['title'],
                'earnedAt' => $this->formatAbsoluteTime((int) $row['date']),
                'earnedAgo' => $this->formatRelativeTime((int) $row['date']),
            ];
        }, $query->getResultArray());

        $this->cache->save($cacheKey, $items, 300);

        return $items;
    }

    public function getRecentGuilds(int $limit): array
    {
        $cacheKey = 'server_recent_guilds_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        try {
            $query = $this->characters->query(
                "SELECT g.guildid,
                        g.name,
                        g.leaderguid,
                        g.createdate,
                        leader.name AS leader_name,
                        COUNT(member.guid) AS members
                 FROM guild g
                 LEFT JOIN characters leader
                    ON leader.guid = g.leaderguid
                 LEFT JOIN guild_member member
                    ON member.guildid = g.guildid
                 GROUP BY g.guildid, g.name, g.leaderguid, g.createdate, leader.name
                 ORDER BY g.createdate DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (Throwable) {
            return [];
        }

        if (!$query || !$query->getNumRows()) {
            return [];
        }

        $guilds = array_map(function ($row) {
            return [
                'guildId' => $row['guildid'],
                'name' => $row['name'],
                'leader' => $row['leader_name'] ?: 'Unknown',
                'members' => $row['members'],
                'createdAt' => $this->formatAbsoluteTime((int) $row['createdate']),
                'createdAgo' => $this->formatRelativeTime((int) $row['createdate']),
            ];
        }, $query->getResultArray());

        $this->cache->save($cacheKey, $guilds, 300);

        return $guilds;
    }

    public function getNewLevelCaps(int $limit): array
    {
        $cacheKey = 'server_recent_caps_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        try {
            $query = $this->characters->query(
                "SELECT ca.guid, ca.date, c.name, c.class, c.race
                 FROM character_achievement ca
                 INNER JOIN " . table('characters', $this->realmId) . " c
                    ON c." . column('characters', 'guid', false, $this->realmId) . " = ca.guid
                 WHERE ca.achievement = 6
                 ORDER BY ca.date DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (Throwable) {
            return [];
        }

        if (!$query || !$query->getNumRows()) {
            return [];
        }

        $items = array_map(function ($row) {
            return [
                'guid' => $row['guid'],
                'name' => $row['name'],
                'class' => $this->realms->getClass((int) $row['class']),
                'race' => $this->realms->getRace((int) $row['race']),
                'earnedAt' => $this->formatAbsoluteTime((int) $row['date']),
                'earnedAgo' => $this->formatRelativeTime((int) $row['date']),
            ];
        }, $query->getResultArray());

        $this->cache->save($cacheKey, $items, 300);

        return $items;
    }

    public function getRealmFirsts(int $limit): array
    {
        $cacheKey = 'server_realm_firsts_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        $worldDatabase = $this->realm->getConfig('world_database');

        try {
            $query = $this->characters->query(
                "SELECT firsts.achievement, firsts.first_date, c.guid, c.name, c.class, c.race,
                        COALESCE(NULLIF(ad.Title_Lang_enUS, ''), CONCAT('Achievement #', firsts.achievement)) AS title,
                        ad.Points AS points
                 FROM (
                    SELECT achievement, MIN(date) AS first_date
                    FROM character_achievement
                    GROUP BY achievement
                    ORDER BY first_date DESC
                    LIMIT ?
                 ) firsts
                 INNER JOIN character_achievement ca
                    ON ca.achievement = firsts.achievement
                   AND ca.date = firsts.first_date
                 INNER JOIN " . table('characters', $this->realmId) . " c
                    ON c." . column('characters', 'guid', false, $this->realmId) . " = ca.guid
                 LEFT JOIN `" . $worldDatabase . "`.achievement_dbc ad
                    ON ad.ID = firsts.achievement
                 GROUP BY firsts.achievement, firsts.first_date, c.guid, c.name, c.class, c.race, title, points
                 ORDER BY firsts.first_date DESC",
                [$limit]
            );
        } catch (Throwable) {
            return [];
        }

        if (!$query || !$query->getNumRows()) {
            return [];
        }

        $items = array_map(function ($row) {
            return [
                'guid' => $row['guid'],
                'name' => $row['name'],
                'class' => $this->realms->getClass((int) $row['class']),
                'race' => $this->realms->getRace((int) $row['race']),
                'title' => $row['title'],
                'points' => $row['points'] ?? 0,
                'earnedAt' => $this->formatAbsoluteTime((int) $row['first_date']),
                'earnedAgo' => $this->formatRelativeTime((int) $row['first_date']),
            ];
        }, $query->getResultArray());

        $this->cache->save($cacheKey, $items, 600);

        return $items;
    }

    private function connect(): void
    {
        $this->realm->getCharacters()->connect();
        $this->characters = $this->realm->getCharacters()->getConnection();
        $this->account = $this->load->database('account', true);
    }

    private function getRealmUptime(int $realmId): string
    {
        $this->connect();

        $query = $this->account->table('uptime')->where('realmid', $realmId)->orderBy('starttime', 'DESC')->get(1);
        $row = $query->getLastRow('array');

        if (!$row || empty($row['starttime'])) {
            return 'Unknown';
        }

        return $this->formatDuration((int) $row['starttime']);
    }

    private function formatDuration(int $startTimestamp): string
    {
        $seconds = max(0, time() - $startTimestamp);

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);

        $parts = [];

        if ($days) {
            $parts[] = $days . 'd';
        }

        if ($hours) {
            $parts[] = $hours . 'h';
        }

        if ($minutes || !$parts) {
            $parts[] = $minutes . 'm';
        }

        return implode(' ', $parts);
    }

    private function formatAbsoluteTime(int $timestamp): string
    {
        if ($timestamp <= 0) {
            return 'Unknown';
        }

        return date('M j, Y H:i', $timestamp);
    }

    private function formatRelativeTime(int $timestamp): string
    {
        if ($timestamp <= 0) {
            return 'Unknown';
        }

        $delta = time() - $timestamp;

        if ($delta < 60) {
            return 'just now';
        }

        if ($delta < 3600) {
            return floor($delta / 60) . 'm ago';
        }

        if ($delta < 86400) {
            return floor($delta / 3600) . 'h ago';
        }

        return floor($delta / 86400) . 'd ago';
    }
}
