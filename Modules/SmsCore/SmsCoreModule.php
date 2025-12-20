<?php

namespace Modules\SmsCore;

use App\Core\Module\AbstractModule;

class SmsCoreModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        // Load routes from Routes/web.php file
        return [
            'web' => __DIR__ . '/Routes/web.php',
            'admin' => __DIR__ . '/Routes/admin.php', // For backward compatibility with /admin/sms routes
        ];
    }

    public function boot(): void
    {
        // Enregistrer les widgets du module
        if (function_exists('register_widget')) {
            register_widget('sms_core', \Modules\SmsCore\Widgets\SmsStatisticsWidget::class);
        }
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'SMS',
                'icon' => 'message-square',
                'permission' => 'access.sms_core',
                'children' => [
                    [
                        'title' => 'Dashboard',
                        'route' => 'admin.sms.index',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Statistiques',
                        'route' => 'admin.sms.statistics',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Envoyer SMS',
                        'route' => 'admin.sms.send',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Envoi en Masse',
                        'route' => 'admin.sms.bulk',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Sender Names',
                        'route' => 'admin.sms.sender_names',
                        'permission' => 'sms.sender_names.view'
                    ],
                    [
                        'title' => 'Historique',
                        'route' => 'admin.sms.history',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Contrats',
                        'route' => 'admin.sms.contracts',
                        'permission' => 'sms.contracts.view'
                    ],
                    [
                        'title' => 'Campagnes',
                        'route' => 'admin.sms.campaigns.index',
                        'permission' => 'sms.campaigns.view'
                    ],
                    [
                        'title' => 'Fournisseurs',
                        'route' => 'admin.sms.providers',
                        'permission' => 'sms.providers.view'
                    ],
                    [
                        'title' => 'Tarification',
                        'route' => 'admin.sms.pricing',
                        'permission' => 'sms.pricing.view'
                    ],
                    [
                        'title' => 'Facturation',
                        'route' => 'admin.sms.billing',
                        'permission' => 'sms.billing.view'
                    ],
                    [
                        'title' => 'API Documentation',
                        'route' => 'admin.sms.api.docs',
                        'permission' => 'access.sms_core'
                    ]
                ]
            ]
        ];
    }
}
