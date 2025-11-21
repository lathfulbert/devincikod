<?php

namespace Modules\Admin;

use App\Core\Module\AbstractModule;

class AdminModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/admin', [\Modules\Admin\Controllers\AdminController::class, 'index']],
            ['GET', '/', [\Modules\Admin\Controllers\AdminController::class, 'index']], // Root route
            ['GET', '/admin/dashboard', [\Modules\Admin\Controllers\AdminController::class, 'dashboard']],

            // Module Management
            ['GET', '/admin/modules', [\Modules\Admin\Controllers\ModuleController::class, 'index']],
            ['POST', '/admin/modules/enable', [\Modules\Admin\Controllers\ModuleController::class, 'enable']],
            ['POST', '/admin/modules/disable', [\Modules\Admin\Controllers\ModuleController::class, 'disable']],
            ['POST', '/admin/modules/install', [\Modules\Admin\Controllers\ModuleController::class, 'install']],
            ['POST', '/admin/modules/uninstall', [\Modules\Admin\Controllers\ModuleController::class, 'uninstall']],

            // Users Management
            ['GET', '/admin/users', [\Modules\Admin\Controllers\UserController::class, 'index']],
            ['GET', '/admin/users/create', [\Modules\Admin\Controllers\UserController::class, 'create']],
            ['POST', '/admin/users/store', [\Modules\Admin\Controllers\UserController::class, 'store']],
            ['GET', '/admin/users/edit/{id}', [\Modules\Admin\Controllers\UserController::class, 'edit']],
            ['POST', '/admin/users/update/{id}', [\Modules\Admin\Controllers\UserController::class, 'update']],
            ['POST', '/admin/users/delete/{id}', [\Modules\Admin\Controllers\UserController::class, 'delete']],

            // Roles Management
            ['GET', '/admin/roles', [\Modules\Admin\Controllers\RoleController::class, 'index']],
            ['GET', '/admin/roles/create', [\Modules\Admin\Controllers\RoleController::class, 'create']],
            ['POST', '/admin/roles/store', [\Modules\Admin\Controllers\RoleController::class, 'store']],
            ['GET', '/admin/roles/edit/{id}', [\Modules\Admin\Controllers\RoleController::class, 'edit']],
            ['POST', '/admin/roles/update/{id}', [\Modules\Admin\Controllers\RoleController::class, 'update']],
            ['POST', '/admin/roles/delete/{id}', [\Modules\Admin\Controllers\RoleController::class, 'delete']],

            // Permissions Management
            ['GET', '/admin/permissions', [\Modules\Admin\Controllers\PermissionController::class, 'index']],
            ['GET', '/admin/permissions/create', [\Modules\Admin\Controllers\PermissionController::class, 'create']],
            ['POST', '/admin/permissions/store', [\Modules\Admin\Controllers\PermissionController::class, 'store']],
            ['GET', '/admin/permissions/edit/{id}', [\Modules\Admin\Controllers\PermissionController::class, 'edit']],
            ['POST', '/admin/permissions/update/{id}', [\Modules\Admin\Controllers\PermissionController::class, 'update']],
            ['POST', '/admin/permissions/delete/{id}', [\Modules\Admin\Controllers\PermissionController::class, 'delete']],
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
