<?php

namespace Modules\WhatsAppMarketing;

use App\Core\Module\AbstractModule;

class WhatsAppMarketingModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        return require __DIR__ . '/Routes/web.php';
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'WhatsApp Marketing',
                'icon' => 'message-circle',
                'children' => [
                    ['title' => 'Tableau de bord', 'url' => '/admin/whatsapp'],
                    ['title' => 'Campagnes', 'url' => '/admin/whatsapp/campaigns'],
                    ['title' => 'Modèles (Templates)', 'url' => '/admin/whatsapp/templates'],
                    ['title' => 'Conversations', 'url' => '/admin/whatsapp/messages'],
                    ['title' => 'Configuration', 'url' => '/admin/whatsapp/gateways'],
                ]
            ]
        ];
    }
}
