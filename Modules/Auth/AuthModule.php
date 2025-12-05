<?php

namespace Modules\Auth;

use App\Core\Module\ModuleContract;

class AuthModule implements ModuleContract
{
    public function getName(): string
    {
        return 'Auth';
    }

    public function getDescription(): string
    {
        return 'Authentication and MFA Module';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return 'LathDevinci';
    }

    public function getDependencies(): array
    {
        return ['Users'];
    }

    public function register(): void
    {
        // Register services
    }

    public function boot(): void
    {
        // Register middleware
        $router = \App\Core\Application::getInstance()->router;
        $router->registerMiddleware('mfa', \Modules\Auth\Middleware\RequireMfa::class);
    }

    public function getRoutes(): array
    {
        return [
            __DIR__ . '/Routes/web.php'
        ];
    }

    public function getApiRoutes(): array
    {
        return [];
    }

    public function getMigrations(): array
    {
        return [];
    }

    public function getPermissions(): array
    {
        return [];
    }

    public function getServices(): array
    {
        return [];
    }

    public function getConfig(): array
    {
        return [];
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

    public function onActivate(): void {}

    public function onDeactivate(): void {}

    public function onInstall(): void {}

    public function onUninstall(): void {}

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'Authentification',
                'icon' => 'lock',
                'children' => [
                    [
                        'title' => 'Paramètres MFA',
                        'url' => '/auth/mfa/settings',
                        'permission' => null
                    ],
                    [
                        'title' => 'Journal d\'audit',
                        'url' => '/admin/auth/logs',
                        'permission' => 'admin.auth.logs.view'
                    ]
                ]
            ]
        ];
    }
}
