<?php

namespace Modules\EmailMarketing;

use App\Core\Module\AbstractModule;

class EmailMarketingModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        // Charger les routes depuis le fichier routes/admin.php et gateways.php
        return [
            'admin' => __DIR__ . '/Routes/admin.php',
            'gateways' => __DIR__ . '/Routes/gateways.php',
        ];
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'Email Marketing',
                'icon' => 'mail',
                'children' => [
                    ['title' => 'Dashboard', 'route' => 'admin.email-marketing.index'],
                    ['title' => 'Campaigns', 'route' => 'admin.email-marketing.campaigns.index'],
                    ['title' => 'Templates', 'route' => 'admin.email-marketing.templates.index'],
                    ['title' => 'Workflows', 'route' => 'admin.email-marketing.workflows.index'],
                    ['title' => 'Analytics', 'route' => 'admin.email-marketing.analytics'],
                    [
                        'title' => 'Gateways Email',
                        'route' => 'admin.email-marketing.gateways.index',
                        'icon' => 'settings',
                        'permission' => 'manage_email_gateways'
                    ],
                ]
            ]
        ];
    }

    /**
     * Services à enregistrer dans le container
     */
    public function getServices(): array
    {
        return [
            'email.gateway.factory' => \Modules\EmailMarketing\Services\EmailGatewayFactory::class,
            'email.sender' => \Modules\EmailMarketing\Services\EmailSenderService::class,
            'email.multichannel' => \Modules\EmailMarketing\Services\MultiChannelService::class,
            'email.analytics' => \Modules\EmailMarketing\Services\CampaignAnalyticsService::class,
        ];
    }
}
