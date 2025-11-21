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

            // Module Management
            ['GET', '/admin/modules', [\Modules\Admin\Controllers\ModuleController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/modules/enable', [\Modules\Admin\Controllers\ModuleController::class, 'enable'], [$authMiddleware]],
            ['POST', '/admin/modules/disable', [\Modules\Admin\Controllers\ModuleController::class, 'disable'], [$authMiddleware]],
            ['POST', '/admin/modules/install', [\Modules\Admin\Controllers\ModuleController::class, 'install'], [$authMiddleware]],
            ['POST', '/admin/modules/uninstall', [\Modules\Admin\Controllers\ModuleController::class, 'uninstall'], [$authMiddleware]],

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
}
