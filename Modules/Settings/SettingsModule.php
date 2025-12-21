<?php

namespace Modules\Settings;

use App\Core\Module\AbstractModule;

class SettingsModule extends AbstractModule
{
    public function getRoutes(): array
    {
        // Charger les routes depuis le fichier routes/admin.php
        return [
            'admin' => __DIR__ . '/Routes/admin.php',
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'Configuration Générale',
                'icon' => 'settings',
                'badge' => null,
                'children' => [
                    ['title' => "Vue d'ensemble", 'route' => 'admin.settings.index', 'icon' => 'grid'],
                    ['title' => 'Paramètres du site', 'route' => 'admin.settings.site', 'icon' => 'globe'],
                    ['title' => 'Thème & Apparence', 'route' => 'admin.settings.theme', 'icon' => 'droplet'],
                    ['title' => 'Mode Maintenance', 'route' => 'admin.maintenance', 'icon' => 'tool'],
                    ['title' => 'Monitoring Système', 'route' => 'admin.health', 'icon' => 'activity'],
                    ['title' => 'Logs Système', 'route' => 'admin.logs.cron', 'icon' => 'file-text'],
                    ['title' => 'API & Services', 'route' => 'admin.settings.api', 'icon' => 'key'],
                    ['title' => 'Configuration Mail', 'route' => 'admin.settings.mail', 'icon' => 'mail'],
                    ['title' => 'Configuration SMS', 'route' => 'admin.settings.sms', 'icon' => 'message-circle'],
                    ['title' => 'Configuration Wallet', 'route' => 'admin.settings.wallet', 'icon' => 'credit-card'],
                    ['title' => 'Langues', 'route' => 'admin.settings.languages', 'icon' => 'globe'],
                    ['title' => 'Traductions', 'route' => 'admin.settings.translations', 'icon' => 'flag'],
                    ['title' => 'Webhooks', 'route' => 'admin.settings.webhooks', 'icon' => 'link'],
                ]
            ]
        ];
    }

    public function getApiRoutes(): array
    {
        return [
            ['GET', '/settings/all', [\Modules\Settings\Controllers\ApiController::class, 'getAllSettings']],
            ['GET', '/settings/{key}', [\Modules\Settings\Controllers\ApiController::class, 'getSetting']],
            ['POST', '/settings/{key}', [\Modules\Settings\Controllers\ApiController::class, 'updateSetting']],
        ];
    }

    public function boot(): void
    {
        // Register settings service
        $app = \App\Core\Application::getInstance();
        $app->singleton('settings', function () {
            return new \Modules\Settings\Services\SettingsService();
        });
    }
}
