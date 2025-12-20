<?php

namespace Modules\Settings;

use App\Core\Module\AbstractModule;

class SettingsModule extends AbstractModule
{
    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // Main Settings Dashboard
            ['GET', '/admin/settings', [\Modules\Settings\Controllers\SettingsController::class, 'index'], [$authMiddleware]],

            // Site Settings
            ['GET', '/admin/settings/site', [\Modules\Settings\Controllers\SiteSettingsController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/settings/site/update', [\Modules\Settings\Controllers\SiteSettingsController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/settings/site/upload-logo', [\Modules\Settings\Controllers\SiteSettingsController::class, 'uploadLogo'], [$authMiddleware]],
            ['POST', '/admin/settings/site/upload-favicon', [\Modules\Settings\Controllers\SiteSettingsController::class, 'uploadFavicon'], [$authMiddleware]],

            // Theme Settings
            ['GET', '/admin/settings/theme', [\Modules\Settings\Controllers\ThemeSettingsController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/settings/theme/update', [\Modules\Settings\Controllers\ThemeSettingsController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/settings/theme/preview', [\Modules\Settings\Controllers\ThemeSettingsController::class, 'preview'], [$authMiddleware]],
            ['POST', '/admin/settings/theme/reset', [\Modules\Settings\Controllers\ThemeSettingsController::class, 'reset'], [$authMiddleware]],

            // API Settings
            ['GET', '/admin/settings/api', [\Modules\Settings\Controllers\ApiSettingsController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/settings/api/update', [\Modules\Settings\Controllers\ApiSettingsController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/settings/api/test-connection', [\Modules\Settings\Controllers\ApiSettingsController::class, 'testConnection'], [$authMiddleware]],
            ['GET', '/admin/settings/api/generate-key', [\Modules\Settings\Controllers\ApiSettingsController::class, 'generateKey'], [$authMiddleware]],

            // Mail Settings
            ['GET', '/admin/settings/mail', [\Modules\Settings\Controllers\MailSettingsController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/settings/mail/update', [\Modules\Settings\Controllers\MailSettingsController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/settings/mail/test', [\Modules\Settings\Controllers\MailSettingsController::class, 'sendTest'], [$authMiddleware]],

            // Language Management
            ['GET', '/admin/settings/languages', [\Modules\Settings\Controllers\LanguageController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/settings/languages/activate', [\Modules\Settings\Controllers\LanguageController::class, 'activate'], [$authMiddleware]],
            ['POST', '/admin/settings/languages/deactivate', [\Modules\Settings\Controllers\LanguageController::class, 'deactivate'], [$authMiddleware]],
            ['POST', '/admin/settings/languages/set-default', [\Modules\Settings\Controllers\LanguageController::class, 'setDefault'], [$authMiddleware]],
            ['POST', '/admin/settings/languages/set-fallback', [\Modules\Settings\Controllers\LanguageController::class, 'setFallback'], [$authMiddleware]],
            ['POST', '/admin/settings/languages/create-file', [\Modules\Settings\Controllers\LanguageController::class, 'createFile'], [$authMiddleware]],

            // Translation Management
            ['GET', '/admin/settings/translations', [\Modules\Settings\Controllers\TranslationController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/settings/translations/create', [\Modules\Settings\Controllers\TranslationController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/settings/translations/store', [\Modules\Settings\Controllers\TranslationController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/settings/translations/{id}/edit', [\Modules\Settings\Controllers\TranslationController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/settings/translations/{id}/update', [\Modules\Settings\Controllers\TranslationController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/settings/translations/{id}/delete', [\Modules\Settings\Controllers\TranslationController::class, 'delete'], [$authMiddleware]],
            ['POST', '/admin/settings/translations/import', [\Modules\Settings\Controllers\TranslationController::class, 'import'], [$authMiddleware]],
            ['GET', '/admin/settings/translations/export', [\Modules\Settings\Controllers\TranslationController::class, 'export'], [$authMiddleware]],
            ['GET', '/admin/settings/translations/history', [\Modules\Settings\Controllers\TranslationController::class, 'history'], [$authMiddleware]],

            // Webhook Settings
            ['GET', '/admin/settings/webhooks', [\Modules\Settings\Controllers\WebhookController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/settings/webhooks/create', [\Modules\Settings\Controllers\WebhookController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/settings/webhooks/store', [\Modules\Settings\Controllers\WebhookController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/settings/webhooks/{id}/edit', [\Modules\Settings\Controllers\WebhookController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/settings/webhooks/{id}/update', [\Modules\Settings\Controllers\WebhookController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/settings/webhooks/{id}/delete', [\Modules\Settings\Controllers\WebhookController::class, 'delete'], [$authMiddleware]],
            ['POST', '/admin/settings/webhooks/{id}/test', [\Modules\Settings\Controllers\WebhookController::class, 'test'], [$authMiddleware]],
            ['GET', '/admin/settings/webhooks/{id}/logs', [\Modules\Settings\Controllers\WebhookController::class, 'logs'], [$authMiddleware]],

            // Backup & Export
            ['GET', '/admin/settings/backup', [\Modules\Settings\Controllers\SettingsController::class, 'backup'], [$authMiddleware]],
            ['POST', '/admin/settings/import', [\Modules\Settings\Controllers\SettingsController::class, 'import'], [$authMiddleware]],

            // SMS Settings
            ['GET', '/admin/settings/sms', [\Modules\Settings\Controllers\SmsSettingsController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/settings/sms/gateways/create', [\Modules\Settings\Controllers\SmsSettingsController::class, 'createGateway'], [$authMiddleware]],
            ['POST', '/admin/settings/sms/gateways/store', [\Modules\Settings\Controllers\SmsSettingsController::class, 'storeGateway'], [$authMiddleware]],
            ['GET', '/admin/settings/sms/gateways/{id}/edit', [\Modules\Settings\Controllers\SmsSettingsController::class, 'editGateway'], [$authMiddleware]],
            ['POST', '/admin/settings/sms/gateways/{id}/update', [\Modules\Settings\Controllers\SmsSettingsController::class, 'updateGateway'], [$authMiddleware]],
            ['POST', '/admin/settings/sms/gateways/{id}/delete', [\Modules\Settings\Controllers\SmsSettingsController::class, 'deleteGateway'], [$authMiddleware]],
            ['GET', '/admin/settings/sms/gateways/{id}/test', [\Modules\Settings\Controllers\SmsSettingsController::class, 'testGateway'], [$authMiddleware]],
            ['POST', '/admin/settings/sms/gateways/{id}/switch-mode', [\Modules\Settings\Controllers\SmsSettingsController::class, 'switchMode'], [$authMiddleware]],
            ['GET', '/admin/settings/sms/gateways/{id}/set-default', [\Modules\Settings\Controllers\SmsSettingsController::class, 'setDefault'], [$authMiddleware]],
            ['GET', '/admin/settings/sms/gateways/{id}/toggle', [\Modules\Settings\Controllers\SmsSettingsController::class, 'toggleStatus'], [$authMiddleware]],

            // Wallet Settings
            ['GET', '/admin/settings/wallet', [\Modules\Settings\Controllers\WalletSettingsController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/settings/wallet/update', [\Modules\Settings\Controllers\WalletSettingsController::class, 'update'], [$authMiddleware]],
            ['GET', '/admin/settings/wallet/gateways/create', [\Modules\Settings\Controllers\WalletSettingsController::class, 'createGateway'], [$authMiddleware]],
            ['POST', '/admin/settings/wallet/gateways/store', [\Modules\Settings\Controllers\WalletSettingsController::class, 'storeGateway'], [$authMiddleware]],
            ['GET', '/admin/settings/wallet/gateways/{id}/edit', [\Modules\Settings\Controllers\WalletSettingsController::class, 'editGateway'], [$authMiddleware]],
            ['POST', '/admin/settings/wallet/gateways/{id}/update', [\Modules\Settings\Controllers\WalletSettingsController::class, 'updateGateway'], [$authMiddleware]],
            ['POST', '/admin/settings/wallet/gateways/{id}/delete', [\Modules\Settings\Controllers\WalletSettingsController::class, 'deleteGateway'], [$authMiddleware]],
            ['GET', '/admin/settings/wallet/gateways/{id}/test', [\Modules\Settings\Controllers\WalletSettingsController::class, 'testGateway'], [$authMiddleware]],
            ['GET', '/admin/settings/wallet/gateways/{id}/set-default', [\Modules\Settings\Controllers\WalletSettingsController::class, 'setDefault'], [$authMiddleware]],
            ['GET', '/admin/settings/wallet/gateways/{id}/toggle', [\Modules\Settings\Controllers\WalletSettingsController::class, 'toggleStatus'], [$authMiddleware]],

            // Maintenance Mode
            ['GET', '/admin/maintenance', [\Modules\Settings\Controllers\MaintenanceController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/maintenance/update', [\Modules\Settings\Controllers\MaintenanceController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/maintenance/toggle', [\Modules\Settings\Controllers\MaintenanceController::class, 'toggle'], [$authMiddleware]],
            ['GET', '/admin/maintenance/remove-background', [\Modules\Settings\Controllers\MaintenanceController::class, 'removeBackgroundImage'], [$authMiddleware]],

            // Health Check / System Monitoring (Admin only)
            ['GET', '/admin/health', [\Modules\Settings\Controllers\HealthCheckController::class, 'index'], [$authMiddleware]],

            // API publique de monitoring (pas d'auth pour monitoring externe)
            ['GET', '/api/health', [\Modules\Settings\Controllers\HealthCheckController::class, 'apiCheck'], []],
            ['GET', '/api/health/badge', [\Modules\Settings\Controllers\HealthCheckController::class, 'badge'], []],

            // Log Viewer (Admin only)
            ['GET', '/admin/logs/cron', [\Modules\Settings\Controllers\LogsController::class, 'cronLogs'], [$authMiddleware]],
            ['GET', '/admin/logs/health', [\Modules\Settings\Controllers\LogsController::class, 'healthLogs'], [$authMiddleware]],
            ['GET', '/admin/logs/sms-queue', [\Modules\Settings\Controllers\LogsController::class, 'smsQueueLogs'], [$authMiddleware]],
            ['POST', '/admin/logs/clear', [\Modules\Settings\Controllers\LogsController::class, 'clearLog'], [$authMiddleware]],
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
