<?php

namespace Modules\Users;

use App\Core\Module\ModuleContract;

class UsersModule implements ModuleContract
{
    public function getName(): string
    {
        return 'Users';
    }

    public function getDescription(): string
    {
        return 'User Management Module';
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
        return ['RBAC'];
    }

    public function register(): void
    {
        // Register services
    }

    public function boot(): void
    {
        // Boot module
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
                'title' => __('users.title'),
                'icon' => 'users',
                'children' => [
                    [
                        'title' => __('users.list'),
                        'url' => '/admin/users',
                        'permission' => 'admin.users.view'
                    ],
                    [
                        'title' => __('users.create_user'),
                        'url' => '/admin/users/create',
                        'permission' => 'admin.users.create'
                    ]
                ]
            ]
        ];
    }
}
