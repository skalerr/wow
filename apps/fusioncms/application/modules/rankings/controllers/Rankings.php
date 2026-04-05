<?php

use MX\MX_Controller;

class Rankings extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        requirePermission('view');

        $this->load->config('rankings/rankings');
        $this->load->model('rankings_model');
        $this->load->model('server/server_model');
    }

    public function index(int|bool $realmId = false)
    {
        $realms = $this->realms->getRealms();

        if (!$realms) {
            $this->template->showError('No realms are configured yet.');
            return;
        }

        if (!$realmId) {
            $realmId = $realms[0]->getId();
        }

        $this->rankings_model->setRealm((int) $realmId);
        $this->server_model->setRealm((int) $realmId);

        $content = $this->template->loadPage('rankings.tpl', [
            'module' => 'rankings',
            'url' => $this->template->page_url,
            'selectedRealmId' => (int) $realmId,
            'realmCards' => $this->server_model->getRealmCards(),
            'topLevelPlayers' => $this->rankings_model->getTopLevelPlayers((int) $this->config->item('top_limit')),
            'topAchievementPlayers' => $this->rankings_model->getTopAchievementPlayers((int) $this->config->item('top_limit')),
            'topKillPlayers' => $this->rankings_model->getTopKillPlayers((int) $this->config->item('top_limit')),
            'topGuilds' => $this->rankings_model->getTopGuilds((int) $this->config->item('top_limit')),
            'teams2v2' => $this->rankings_model->getArenaTeams(2, (int) $this->config->item('arena_limit')),
            'teams3v3' => $this->rankings_model->getArenaTeams(3, (int) $this->config->item('arena_limit')),
            'teams5v5' => $this->rankings_model->getArenaTeams(5, (int) $this->config->item('arena_limit')),
        ]);

        $page = $this->template->loadPage('page.tpl', [
            'module' => 'default',
            'headline' => 'Rankings',
            'content' => $content,
        ]);

        $this->template->view($page, 'modules/rankings/css/rankings.css');
    }
}
