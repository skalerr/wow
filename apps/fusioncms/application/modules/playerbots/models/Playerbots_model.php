<?php

use CodeIgniter\Database\BaseConnection;

class Playerbots_model extends CI_Model
{
    private Realm $realm;
    private int $realmId = 1;
    private BaseConnection $characters;
    private BaseConnection $account;
    private ?string $playerbotsDatabase = null;
    private ?bool $hasAccountTypeTable = null;
    private ?bool $hasEventsTable = null;

    public function setRealm(int $realmId): void
    {
        $this->realmId = $realmId;
        $this->realm = $this->realms->getRealm($realmId);
    }

    public function getRealmCards(): array
    {
        $cards = [];

        foreach ($this->realms->getRealms() as $realm) {
            $cards[] = [
                'id' => $realm->getId(),
                'name' => $realm->getName(),
                'online' => $realm->isOnline(true),
                'players' => $realm->isOnline(true) ? $realm->getOnline() : 0,
            ];
        }

        return $cards;
    }

    public function getOverview(): array
    {
        $cacheKey = 'playerbots_overview_' . $this->realmId;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        $overview = [
            'available' => $this->hasAccountTypeTable() || $this->hasRndbotAccounts(),
            'botDetection' => $this->hasAccountTypeTable() ? 'playerbots_account_type' : 'rndbot account prefix fallback',
            'onlineRandomBots' => 0,
            'randomBotAccounts' => 0,
            'playerbotsDatabase' => $this->getPlayerbotsDatabaseName(),
        ];

        $query = $this->characters->query(
            "SELECT COUNT(*) AS total
             FROM " . table('characters', $this->realmId) . " c
             INNER JOIN {$this->account->database}.account a
                ON a.id = c." . column('characters', 'account', false, $this->realmId) . "
             {$this->getAccountTypeJoin()}
             WHERE c." . column('characters', 'online', false, $this->realmId) . " = 1
               AND {$this->getRandomBotPredicate('a')}"
        );

        $accountQuery = $this->account->query(
            "SELECT COUNT(*) AS total
             FROM account a
             {$this->getAccountTypeJoin('a', false)}
             WHERE {$this->getRandomBotPredicate('a')}"
        );

        $overview['onlineRandomBots'] = (int) ($query?->getLastRow('array')['total'] ?? 0);
        $overview['randomBotAccounts'] = (int) ($accountQuery?->getLastRow('array')['total'] ?? 0);

        $this->cache->save($cacheKey, $overview, (int) $this->config->item('cache_seconds'));

        return $overview;
    }

    public function getLevelBrackets(): array
    {
        $cacheKey = 'playerbots_level_brackets_' . $this->realmId;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        $query = $this->characters->query(
            "SELECT
                SUM(CASE WHEN c.level BETWEEN 1 AND 19 THEN 1 ELSE 0 END) AS bracket_1_19,
                SUM(CASE WHEN c.level BETWEEN 20 AND 39 THEN 1 ELSE 0 END) AS bracket_20_39,
                SUM(CASE WHEN c.level BETWEEN 40 AND 59 THEN 1 ELSE 0 END) AS bracket_40_59,
                SUM(CASE WHEN c.level BETWEEN 60 AND 69 THEN 1 ELSE 0 END) AS bracket_60_69,
                SUM(CASE WHEN c.level BETWEEN 70 AND 79 THEN 1 ELSE 0 END) AS bracket_70_79,
                SUM(CASE WHEN c.level = 80 THEN 1 ELSE 0 END) AS bracket_80
             FROM " . table('characters', $this->realmId) . " c
             INNER JOIN {$this->account->database}.account a
                ON a.id = c." . column('characters', 'account', false, $this->realmId) . "
             {$this->getAccountTypeJoin()}
             WHERE c." . column('characters', 'online', false, $this->realmId) . " = 1
               AND {$this->getRandomBotPredicate('a')}"
        );

        $row = $query?->getLastRow('array') ?? [];

        $brackets = [
            ['label' => '1-19', 'count' => (int) ($row['bracket_1_19'] ?? 0)],
            ['label' => '20-39', 'count' => (int) ($row['bracket_20_39'] ?? 0)],
            ['label' => '40-59', 'count' => (int) ($row['bracket_40_59'] ?? 0)],
            ['label' => '60-69', 'count' => (int) ($row['bracket_60_69'] ?? 0)],
            ['label' => '70-79', 'count' => (int) ($row['bracket_70_79'] ?? 0)],
            ['label' => '80', 'count' => (int) ($row['bracket_80'] ?? 0)],
        ];

        $this->cache->save($cacheKey, $brackets, (int) $this->config->item('cache_seconds'));

        return $brackets;
    }

    public function getRoleBreakdown(): array
    {
        $cacheKey = 'playerbots_role_breakdown_' . $this->realmId;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        $query = $this->characters->query(
            "SELECT
                SUM(CASE WHEN c.class IN (1, 2, 6, 11) THEN 1 ELSE 0 END) AS tank_capable,
                SUM(CASE WHEN c.class IN (2, 5, 7, 11) THEN 1 ELSE 0 END) AS healer_capable,
                SUM(CASE WHEN c.class IN (3, 4, 8, 9) THEN 1 ELSE 0 END) AS damage_focused
             FROM " . table('characters', $this->realmId) . " c
             INNER JOIN {$this->account->database}.account a
                ON a.id = c." . column('characters', 'account', false, $this->realmId) . "
             {$this->getAccountTypeJoin()}
             WHERE c." . column('characters', 'online', false, $this->realmId) . " = 1
               AND {$this->getRandomBotPredicate('a')}"
        );

        $row = $query?->getLastRow('array') ?? [];

        $roles = [
            ['label' => 'Tank-capable', 'count' => (int) ($row['tank_capable'] ?? 0)],
            ['label' => 'Healer-capable', 'count' => (int) ($row['healer_capable'] ?? 0)],
            ['label' => 'Damage-focused', 'count' => (int) ($row['damage_focused'] ?? 0)],
        ];

        $this->cache->save($cacheKey, $roles, (int) $this->config->item('cache_seconds'));

        return $roles;
    }

    public function getRecentEvents(int $limit): array
    {
        $cacheKey = 'playerbots_recent_events_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        if (!$this->hasEventsTable()) {
            return [];
        }

        $this->connect();

        $query = $this->account->query(
            "SELECT pb.event, pb.time, pb.value, pb.data,
                    c." . column('characters', 'guid', false, $this->realmId) . " AS guid,
                    c." . column('characters', 'name', false, $this->realmId) . " AS name,
                    c." . column('characters', 'level', false, $this->realmId) . " AS level,
                    c." . column('characters', 'class', false, $this->realmId) . " AS class
             FROM {$this->getPlayerbotsDatabaseName()}.playerbots_random_bots pb
             LEFT JOIN " . $this->realm->getConfig('characters_database') . "." . table('characters', $this->realmId) . " c
                ON c." . column('characters', 'guid', false, $this->realmId) . " = pb.bot
             ORDER BY pb.time DESC
             LIMIT ?",
            [$limit]
        );

        if (!$query || !$query->getNumRows()) {
            return [];
        }

        $events = array_map(function ($row) {
            return [
                'guid' => $row['guid'],
                'name' => $row['name'] ?: 'Unknown bot',
                'level' => $row['level'] ?: '?',
                'class' => !empty($row['class']) ? $this->realms->getClass((int) $row['class']) : 'Unknown',
                'event' => $this->humanizeEvent((string) $row['event']),
                'value' => $row['value'],
                'data' => $row['data'],
                'time' => $this->formatRelativeTime((int) $row['time']),
            ];
        }, $query->getResultArray());

        $this->cache->save($cacheKey, $events, (int) $this->config->item('cache_seconds'));

        return $events;
    }

    private function connect(): void
    {
        $this->realm->getCharacters()->connect();
        $this->characters = $this->realm->getCharacters()->getConnection();
        $this->account = $this->load->database('account', true);
    }

    private function getPlayerbotsDatabaseName(): string
    {
        if ($this->playerbotsDatabase !== null) {
            return $this->playerbotsDatabase;
        }

        $this->connect();

        $accountDb = $this->account->database;

        if (str_contains($accountDb, 'auth')) {
            $this->playerbotsDatabase = str_replace('auth', 'playerbots', $accountDb);
        } else {
            $this->playerbotsDatabase = $accountDb . '_playerbots';
        }

        return $this->playerbotsDatabase;
    }

    private function hasAccountTypeTable(): bool
    {
        if ($this->hasAccountTypeTable !== null) {
            return $this->hasAccountTypeTable;
        }

        $this->connect();

        try {
            $query = $this->account->query(
                "SELECT COUNT(*) AS total
                 FROM information_schema.tables
                 WHERE table_schema = ?
                   AND table_name = 'playerbots_account_type'",
                [$this->getPlayerbotsDatabaseName()]
            );
        } catch (Throwable) {
            $this->hasAccountTypeTable = false;

            return $this->hasAccountTypeTable;
        }

        $this->hasAccountTypeTable = (int) ($query?->getLastRow('array')['total'] ?? 0) > 0;

        return $this->hasAccountTypeTable;
    }

    private function hasEventsTable(): bool
    {
        if ($this->hasEventsTable !== null) {
            return $this->hasEventsTable;
        }

        $this->connect();

        try {
            $query = $this->account->query(
                "SELECT COUNT(*) AS total
                 FROM information_schema.tables
                 WHERE table_schema = ?
                   AND table_name = 'playerbots_random_bots'",
                [$this->getPlayerbotsDatabaseName()]
            );
        } catch (Throwable) {
            $this->hasEventsTable = false;

            return $this->hasEventsTable;
        }

        $this->hasEventsTable = (int) ($query?->getLastRow('array')['total'] ?? 0) > 0;

        return $this->hasEventsTable;
    }

    private function hasRndbotAccounts(): bool
    {
        $this->connect();

        $query = $this->account->query("SELECT COUNT(*) AS total FROM account WHERE username LIKE 'rndbot%'");

        return (int) ($query?->getLastRow('array')['total'] ?? 0) > 0;
    }

    private function getAccountTypeJoin(string $accountAlias = 'a', bool $withLeadingSpace = true): string
    {
        if (!$this->hasAccountTypeTable()) {
            return '';
        }

        $join = "LEFT JOIN {$this->getPlayerbotsDatabaseName()}.playerbots_account_type pat ON pat.account_id = {$accountAlias}.id";

        return $withLeadingSpace ? ' ' . $join : $join;
    }

    private function getRandomBotPredicate(string $accountAlias): string
    {
        if ($this->hasAccountTypeTable()) {
            return "(pat.account_type = 1 OR {$accountAlias}.username LIKE 'rndbot%')";
        }

        return "{$accountAlias}.username LIKE 'rndbot%'";
    }

    private function humanizeEvent(string $event): string
    {
        if ($event === '') {
            return 'Runtime update';
        }

        return ucwords(str_replace(['_', '-'], ' ', $event));
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
