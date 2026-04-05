<?php

use MX\MX_Controller;

class Playerbots extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        requirePermission('view');

        $this->load->config('playerbots');
        $this->load->model('playerbots_model');
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

        $this->playerbots_model->setRealm((int) $realmId);

        $faq = [
            [
                'title' => 'Dungeon groups',
                'body' => 'Random bots are best used to fill missing party slots, help with questing and keep dungeon groups moving when the server population dips.',
            ],
            [
                'title' => 'Group play',
                'body' => 'Bots can behave like party members instead of static NPC helpers. They follow, fight, recover and keep the world populated even outside peak hours.',
            ],
            [
                'title' => 'Summoning',
                'body' => 'Summon behaviour depends on the playerbots configuration. On this server the page tracks live bot stats, while in-game summon rules still follow playerbots.conf.',
            ],
            [
                'title' => 'Guildhouse and utilities',
                'body' => 'Guildhouse, teleport or custom QoL systems stay separate from playerbots. This page focuses on how many bots are live and how the random bot pool is distributed.',
            ],
        ];

        $features = [
            'Random bots populate the world and keep zones active outside prime time.',
            'Bots can help round out dungeon groups and long progression sessions.',
            'The server can tune random bot density without requiring real players to stay online.',
        ];

        $content = $this->template->loadPage('playerbots.tpl', [
            'module' => 'playerbots',
            'url' => $this->template->page_url,
            'selectedRealmId' => (int) $realmId,
            'realmCards' => $this->playerbots_model->getRealmCards(),
            'overview' => $this->playerbots_model->getOverview(),
            'levelBrackets' => $this->playerbots_model->getLevelBrackets(),
            'roleBreakdown' => $this->playerbots_model->getRoleBreakdown(),
            'recentEvents' => $this->playerbots_model->getRecentEvents((int) $this->config->item('events_limit')),
            'features' => $features,
            'faq' => $faq,
        ]);

        $page = $this->template->loadPage('page.tpl', [
            'module' => 'default',
            'headline' => 'Playerbots',
            'content' => $content,
        ]);

        $this->template->view($page, 'modules/playerbots/css/playerbots.css');
    }
}
