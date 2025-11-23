<?php

namespace Modules\Admin;

use App\Core\Module\AbstractModule;

class AdminModule extends AbstractModule
{
    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // Dashboard and base routes
            ['GET', '/admin', [\Modules\Admin\Controllers\AdminController::class, 'index'], [$authMiddleware]],
            ['GET', '/', [\Modules\Admin\Controllers\AdminController::class, 'index'], [$authMiddleware]], // Root route
            ['GET', '/admin/dashboard', [\Modules\Admin\Controllers\AdminController::class, 'dashboard'], [$authMiddleware]],

            // Monitoring Routes
            ['GET', '/admin/monitoring', [\Modules\Admin\Controllers\MonitoringController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/monitoring/clear', [\Modules\Admin\Controllers\MonitoringController::class, 'clear'], [$authMiddleware]],

            // Module Management
            ['GET', '/admin/modules', [\Modules\Admin\Controllers\ModuleController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/modules/upload', [\Modules\Admin\Controllers\ModuleController::class, 'upload'], [$authMiddleware]],
            ['POST', '/admin/modules/process-upload', [\Modules\Admin\Controllers\ModuleController::class, 'processUpload'], [$authMiddleware]],
            ['POST', '/admin/modules/enable', [\Modules\Admin\Controllers\ModuleController::class, 'enable'], [$authMiddleware]],
            ['POST', '/admin/modules/disable', [\Modules\Admin\Controllers\ModuleController::class, 'disable'], [$authMiddleware]],
            ['POST', '/admin/modules/install', [\Modules\Admin\Controllers\ModuleController::class, 'install'], [$authMiddleware]],
            ['POST', '/admin/modules/uninstall', [\Modules\Admin\Controllers\ModuleController::class, 'uninstall'], [$authMiddleware]],
            ['POST', '/admin/modules/{name}/delete', [\Modules\Admin\Controllers\ModuleController::class, 'delete'], [$authMiddleware]],

            // Users Management
            ['GET', '/admin/users', [\Modules\Admin\Controllers\UserController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/users/create', [\Modules\Admin\Controllers\UserController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/users/store', [\Modules\Admin\Controllers\UserController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/users/{id}/edit', [\Modules\Admin\Controllers\UserController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/users/{id}/update', [\Modules\Admin\Controllers\UserController::class, 'update'], [$authMiddleware]],
            ['GET', '/admin/users/{id}/delete', [\Modules\Admin\Controllers\UserController::class, 'delete'], [$authMiddleware]],
            ['POST', '/admin/users/{id}/delete', [\Modules\Admin\Controllers\UserController::class, 'delete'], [$authMiddleware]],

            // Roles Management
            ['GET', '/admin/roles', [\Modules\Admin\Controllers\RoleController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/roles/create', [\Modules\Admin\Controllers\RoleController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/roles/store', [\Modules\Admin\Controllers\RoleController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/roles/{id}/edit', [\Modules\Admin\Controllers\RoleController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/roles/{id}/update', [\Modules\Admin\Controllers\RoleController::class, 'update'], [$authMiddleware]],
            ['GET', '/admin/roles/{id}/delete', [\Modules\Admin\Controllers\RoleController::class, 'delete'], [$authMiddleware]],
            ['POST', '/admin/roles/{id}/delete', [\Modules\Admin\Controllers\RoleController::class, 'delete'], [$authMiddleware]],

            // Permissions Management
            ['GET', '/admin/permissions', [\Modules\Admin\Controllers\PermissionController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/permissions/create', [\Modules\Admin\Controllers\PermissionController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/permissions/store', [\Modules\Admin\Controllers\PermissionController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/permissions/{id}/edit', [\Modules\Admin\Controllers\PermissionController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/permissions/{id}/update', [\Modules\Admin\Controllers\PermissionController::class, 'update'], [$authMiddleware]],
            ['GET', '/admin/permissions/{id}/delete', [\Modules\Admin\Controllers\PermissionController::class, 'delete'], [$authMiddleware]],
            ['POST', '/admin/permissions/{id}/delete', [\Modules\Admin\Controllers\PermissionController::class, 'delete'], [$authMiddleware]],
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
                'type' => 'link',
                'title' => 'Dashboard',
                'icon' => 'home',
                'url' => '/admin/dashboard',
                'class' => 'link-nav'
            ],
            [
                'type' => 'link',
                'title' => 'Monitoring',
                'icon' => 'activity',
                'url' => '/admin/monitoring',
                'class' => 'link-nav'
            ],
            [
                'type' => 'separator',
                'label' => 'Gestion',
                'class' => 'badge-light-primary'
            ],
            [
                'type' => 'dropdown',
                'title' => 'Utilisateurs',
                'icon' => 'users',
                'children' => [
                    ['title' => 'Liste des utilisateurs', 'url' => '/admin/users'],
                    ['title' => 'Rôles', 'url' => '/admin/roles'],
                    ['title' => 'Permissions', 'url' => '/admin/permissions'],
                ]
            ],
            [
                'type' => 'separator',
                'label' => 'Système',
                'class' => 'badge-light-secondary'
            ],
            [
                'type' => 'dropdown',
                'title' => 'Configuration',
                'icon' => 'settings',
                'children' => [
                    ['title' => 'Modules', 'url' => '/admin/modules'],
                    ['title' => 'Cache', 'url' => '/admin/cache'],
                ]
            ],
            [
                'type' => 'link',
                'title' => 'Retour au site',
                'icon' => 'external-link',
                'url' => '/',
                'class' => 'link-nav'
            ]
        ];
    }

    public function getApiRoutes(): array
    {
        return [
            ['GET', '/users/datatable', [\Modules\Admin\Controllers\UserApiController::class, 'datatable']],
        ];
    }
}
