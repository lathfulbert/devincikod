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

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'SMS',
                'icon' => 'message-circle',
                'children' => [
                    ['title' => 'Dashboard', 'url' => '/admin/sms'],
                    ['title' => 'Send SMS', 'url' => '/admin/sms/send'],
                    ['title' => 'Bulk SMS', 'url' => '/admin/sms/bulk'],
                    ['title' => 'Campaigns', 'url' => '/admin/sms/campaigns'],
                    ['title' => 'History', 'url' => '/admin/sms/history'],
                    ['title' => 'Statistics', 'url' => '/admin/sms/statistics'],
                    ['title' => 'Sender Names', 'url' => '/sms/sender-names'],
                    ['title' => 'Tarification', 'url' => '/admin/sms/pricing'],
                    ['title' => 'Facturation', 'url' => '/admin/sms/billing'],
                    ['title' => 'Fournisseurs (Stats)', 'url' => '/admin/sms/providers', 'icon' => 'server'],
                    ['title' => '---', 'url' => '#'], // Separator
                    ['title' => 'Documentation API', 'url' => '/admin/sms/api/docs', 'icon' => 'book'],
                    ['title' => 'Mes clés API', 'url' => '/admin/api-keys', 'icon' => 'key'],
                ]
            ]
        ];
    }
}
