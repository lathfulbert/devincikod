<?php

namespace Modules\RBAC;

use App\Core\Module\AbstractModule;

class RBACModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            __DIR__ . '/Routes/web.php'
        ];
    }

    public function boot(): void
    {
        // Load permissions and seed default roles
        $loader = new \Modules\RBAC\Services\PermissionLoader();
        $loader->scanAndLoad();
        $loader->seedDefaultRoles();
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
                'title' => __('rbac.roles.title') . ' & ' . __('rbac.permissions.title'),
                'icon' => 'shield',
                'children' => [
                    [
                        'title' => __('rbac.roles.list'),
                        'url' => '/admin/roles',
                        'permission' => 'admin.roles.view'
                    ],
                    [
                        'title' => __('rbac.permissions.list'),
                        'url' => '/admin/permissions',
                        'permission' => 'admin.permissions.view'
                    ]
                ]
            ]
        ];
    }
}
