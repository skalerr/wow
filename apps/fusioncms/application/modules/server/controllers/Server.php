<?php

use MX\MX_Controller;

class Server extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        requirePermission('view');

        $this->load->config('server/server');
        $this->load->config('sidebox_discord/discord');
        $this->load->model('server_model');
        $this->load->model('gallery_model');
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

        if (!$this->realms->realmExists($realmId)) {
            $this->template->showError('Invalid realm selected.');
            return;
        }

        $this->server_model->setRealm((int) $realmId);

        $this->template->setTitle('Server');

        $content = $this->template->loadPage('server.tpl', [
            'module' => 'server',
            'url' => $this->template->page_url,
            'selectedRealmId' => (int) $realmId,
            'realmCards' => $this->server_model->getRealmCards(),
            'onlineSpotlight' => $this->server_model->getOnlineSpotlight((int) $this->config->item('spotlight_limit')),
            'activeZones' => $this->server_model->getActiveZones((int) $this->config->item('active_zones_limit')),
            'recentAchievements' => $this->server_model->getRecentAchievements((int) $this->config->item('recent_achievements_limit')),
            'recentGuilds' => $this->server_model->getRecentGuilds((int) $this->config->item('recent_guilds_limit')),
            'newLevelCaps' => $this->server_model->getNewLevelCaps((int) $this->config->item('recent_guilds_limit')),
            'realmFirsts' => $this->server_model->getRealmFirsts((int) $this->config->item('recent_achievements_limit')),
            'gallery' => $this->gallery_model->getAll() ?: (array) $this->config->item('gallery'),
            'discordInvite' => $this->config->item('invite_link'),
            'discordServerId' => $this->config->item('server_id'),
        ]);

        $page = $this->template->loadPage('page.tpl', [
            'module' => 'default',
            'headline' => 'Server Pulse',
            'content' => $content,
        ]);

        $this->template->view($page, 'modules/server/css/server.css');
    }
}
