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
                        'url' => '/admin/sms',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Statistiques',
                        'url' => '/admin/sms/statistics',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Envoyer SMS',
                        'url' => '/admin/sms/send',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Envoi en Masse',
                        'url' => '/admin/sms/bulk',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Sender Names',
                        'url' => '/admin/sms/sender-names',
                        'permission' => 'sms.sender_names.view'
                    ],
                    [
                        'title' => 'Historique',
                        'url' => '/admin/sms/history',
                        'permission' => 'access.sms_core'
                    ],
                    [
                        'title' => 'Contrats',
                        'url' => '/admin/sms/contracts',
                        'permission' => 'sms.contracts.view'
                    ],
                    [
                        'title' => 'Campagnes',
                        'url' => '/admin/sms/campaigns',
                        'permission' => 'sms.campaigns.view'
                    ],
                    [
                        'title' => 'Fournisseurs',
                        'url' => '/admin/sms/providers',
                        'permission' => 'sms.providers.view'
                    ],
                    [
                        'title' => 'Tarification',
                        'url' => '/admin/sms/pricing',
                        'permission' => 'sms.pricing.view'
                    ],
                    [
                        'title' => 'Facturation',
                        'url' => '/admin/sms/billing',
                        'permission' => 'sms.billing.view'
                    ],
                    [
                        'title' => 'API Documentation',
                        'url' => '/admin/sms/api/docs',
                        'permission' => 'access.sms_core'
                    ]
                ]
            ]
        ];
    }
}
