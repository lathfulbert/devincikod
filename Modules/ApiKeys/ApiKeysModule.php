<?php

namespace Modules\ApiKeys;

use App\Core\Module\ModuleContract;
use Modules\ApiKeys\Controllers\ApiKeyController;

class ApiKeysModule implements ModuleContract
{
    public function getName(): string
    {
        return 'ApiKeys';
    }

    public function getDescription(): string
    {
        return 'Manage API keys for all system modules and endpoints';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return 'SunuFramework';
    }

    public function getDependencies(): array
    {
        return []; // No dependencies
    }

    public function register(): void
    {
        // Register services if needed
    }

    public function boot(): void
    {
        // Boot module services
    }

    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // API Analytics Routes (more specific routes first)
            ['GET', '/admin/system-api-keys/analytics/chart-data', [\Modules\ApiKeys\Controllers\ApiAnalyticsController::class, 'chartData'], [$authMiddleware]],
            ['GET', '/admin/system-api-keys/analytics/export', [\Modules\ApiKeys\Controllers\ApiAnalyticsController::class, 'export'], [$authMiddleware]],
            ['GET', '/admin/system-api-keys/analytics', [\Modules\ApiKeys\Controllers\ApiAnalyticsController::class, 'index'], [$authMiddleware]],

            // API Monitoring Routes (more specific routes first)
            ['GET', '/admin/system-api-keys/monitoring/health-status', [\Modules\ApiKeys\Controllers\ApiMonitoringController::class, 'healthStatus'], [$authMiddleware]],
            ['GET', '/admin/system-api-keys/monitoring/anomalies', [\Modules\ApiKeys\Controllers\ApiMonitoringController::class, 'anomalies'], [$authMiddleware]],
            ['GET', '/admin/system-api-keys/monitoring/details', [\Modules\ApiKeys\Controllers\ApiMonitoringController::class, 'details'], [$authMiddleware]],
            ['GET', '/admin/system-api-keys/monitoring', [\Modules\ApiKeys\Controllers\ApiMonitoringController::class, 'index'], [$authMiddleware]],

            // API Keys Management Routes
            ['POST', '/admin/system-api-keys/store', [ApiKeyController::class, 'store'], [$authMiddleware]],
            ['POST', '/admin/system-api-keys/{id}/revoke', [ApiKeyController::class, 'revoke'], [$authMiddleware]],
            ['POST', '/admin/system-api-keys/{id}/activate', [ApiKeyController::class, 'activate'], [$authMiddleware]],
            ['POST', '/admin/system-api-keys/{id}/delete', [ApiKeyController::class, 'destroy'], [$authMiddleware]],
            ['GET', '/admin/system-api-keys', [ApiKeyController::class, 'index'], [$authMiddleware]],
        ];
    }

    public function getMigrations(): array
    {
        return [
            \Modules\ApiKeys\Database\Migrations\CreateApiKeysTable::class,
            \Modules\ApiKeys\Database\Migrations\CreateApiRequestLogsTable::class
        ];
    }

    public function getPermissions(): array
    {
        return [
            'api-keys.view',
            'api-keys.create',
            'api-keys.revoke',
            'api-keys.delete'
        ];
    }

    public function getServices(): array
    {
        return []; // No services to register
    }

    public function getConfig(): array
    {
        return [
            'key_prefix' => 'sk_live',
            'key_length' => 48,
            'max_keys_per_user' => 10
        ];
    }

    public function getAssets(): array
    {
        return [
            'js' => [],
            'css' => []
        ];
    }

    public function getViews(): string
    {
        return __DIR__ . '/Views';
    }

    public function onActivate(): void
    {
        // Run migrations when activated
    }

    public function onDeactivate(): void
    {
        // Cleanup on deactivation
    }

    public function onInstall(): void
    {
        // Installation hooks
    }

    public function onUninstall(): void
    {
        // Uninstallation hooks
    }

    public function getApiRoutes(): array
    {
        return []; // No public API routes, only management routes
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'API Keys',
                'icon' => 'key',
                'children' => [
                    [
                        'title' => 'System API Keys',
                        'url' => '/admin/system-api-keys',
                        'permission' => 'api-keys.view'
                    ],
                    [
                        'title' => 'API Analytics',
                        'url' => '/admin/system-api-keys/analytics',
                        'permission' => 'api-keys.view'
                    ],
                    [
                        'title' => 'API Monitoring',
                        'url' => '/admin/system-api-keys/monitoring',
                        'permission' => 'api-keys.view'
                    ]
                ]
            ]
        ];
    }
}
