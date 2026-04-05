<?php

use MX\MX_Controller;

class Howtoplay extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        requirePermission('view');

        $this->load->config('howtoplay/howtoplay');
        $this->load->config('sidebox_discord/discord');
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

        $this->server_model->setRealm((int) $realmId);
        $realmCards = $this->server_model->getRealmCards();
        $selectedRealm = null;

        foreach ($realmCards as $realmCard) {
            if ($realmCard['id'] === (int) $realmId) {
                $selectedRealm = $realmCard;
                break;
            }
        }

        $content = $this->template->loadPage('howtoplay.tpl', [
            'module' => 'howtoplay',
            'url' => $this->template->page_url,
            'selectedRealmId' => (int) $realmId,
            'selectedRealm' => $selectedRealm,
            'realmCards' => $realmCards,
            'realmlist' => $this->config->item('realmlist'),
            'downloads' => (array) $this->config->item('downloads'),
            'addons' => (array) $this->config->item('addons'),
            'faq' => (array) $this->config->item('faq'),
            'discordInvite' => $this->config->item('invite_link'),
            'discordServerId' => $this->config->item('server_id'),
        ]);

        $page = $this->template->loadPage('page.tpl', [
            'module' => 'default',
            'headline' => 'How to Play',
            'content' => $content,
        ]);

        $this->template->view($page, 'modules/howtoplay/css/howtoplay.css');
    }
}
