<?php

use CodeIgniter\Database\BaseConnection;

class Rankings_model extends CI_Model
{
    private Realm $realm;
    private int $realmId = 1;
    private BaseConnection $connection;

    public function __construct()
    {
        parent::__construct();

        $this->load->config('rankings/rankings');
        $this->load->model('sidebox_top/top_model');
        $this->load->model('pvp_statistics/data_model');
    }

    public function setRealm(int $realmId): void
    {
        $this->realmId = $realmId;
        $this->realm = $this->realms->getRealm($realmId);
        $this->top_model->setRealm($realmId);
        $this->data_model->setRealm($realmId);
    }

    public function getTopLevelPlayers(int $limit): array
    {
        $cacheKey = 'rankings_level_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $this->connect();

        $query = $this->connection->query(
            "SELECT " . columns('characters', ['guid', 'name', 'level', 'class', 'race', 'gender', 'money', 'online'], $this->realmId) . "
             FROM " . table('characters', $this->realmId) . "
             WHERE " . column('characters', 'name', false, $this->realmId) . " <> ''
             ORDER BY " . column('characters', 'level', false, $this->realmId) . " DESC,
                      " . column('characters', 'money', false, $this->realmId) . " DESC,
                      " . column('characters', 'name', false, $this->realmId) . " ASC
             LIMIT ?",
            [$limit]
        );

        if (!$query || !$query->getNumRows()) {
            return [];
        }

        $rank = 1;
        $players = [];

        foreach ($query->getResultArray() as $row) {
            $players[] = [
                'rank' => $rank++,
                'guid' => $row['guid'],
                'name' => $row['name'],
                'level' => $row['level'],
                'class' => $this->realms->getClass((int) $row['class']),
                'race' => $this->realms->getRace((int) $row['race']),
                'online' => !empty($row['online']),
            ];
        }

        $this->cache->save($cacheKey, $players, (int) $this->config->item('cache_seconds'));

        return $players;
    }

    public function getTopAchievementPlayers(int $limit): array
    {
        $cacheKey = 'rankings_achievement_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $data = $this->top_model->getTopAchievementPlayers($limit) ?: [];
        $this->cache->save($cacheKey, $data, (int) $this->config->item('cache_seconds'));

        return $data;
    }

    public function getTopKillPlayers(int $limit): array
    {
        $cacheKey = 'rankings_kills_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $data = $this->top_model->getTotalPlayers($limit) ?: [];
        $this->cache->save($cacheKey, $data, (int) $this->config->item('cache_seconds'));

        return $data;
    }

    public function getTopGuilds(int $limit): array
    {
        $cacheKey = 'rankings_guilds_' . $this->realmId . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $data = $this->top_model->getTopGuild($limit) ?: [];
        $this->cache->save($cacheKey, $data, (int) $this->config->item('cache_seconds'));

        return $data;
    }

    public function getArenaTeams(int $type, int $limit): array
    {
        $cacheKey = 'rankings_arena_' . $this->realmId . '_' . $type . '_' . $limit;
        $cache = $this->cache->get($cacheKey);

        if ($cache !== false) {
            return $cache;
        }

        $data = $this->data_model->getTeams($limit, $type) ?: [];
        $this->cache->save($cacheKey, $data, (int) $this->config->item('cache_seconds'));

        return $data;
    }

    private function connect(): void
    {
        $this->realm->getCharacters()->connect();
        $this->connection = $this->realm->getCharacters()->getConnection();
    }
}
