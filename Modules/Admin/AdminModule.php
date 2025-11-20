<?php

namespace Modules\Admin;

use App\Core\Module\ModuleContract;
use Modules\Admin\Controllers\AdminController;
use Modules\Admin\Controllers\UserController;
use Modules\Admin\Controllers\RoleController;
use Modules\Admin\Controllers\PermissionController;
use Modules\Admin\Controllers\ModuleController;
use App\Core\Auth\Auth;

class AdminModule implements ModuleContract
{
    public function getName(): string
    {
        return 'Admin';
    }

    public function register(): void {}

    public function boot(): void {}

    public function getRoutes(): array
    {
        $adminMiddleware = function () {
            $auth = new Auth();
            if (!$auth->check()) {
                redirect('/login');
                return false;
            }
            // TODO: Add check for admin role/permission
            return true;
        };

        return [
            // Dashboard
            [
                'method' => 'GET',
                'path' => '/admin/dashboard',
                'handler' => [new AdminController(), 'index'],
                'middleware' => [$adminMiddleware]
            ],

            // Users
            [
                'method' => 'GET',
                'path' => '/admin/users',
                'handler' => [new UserController(), 'index'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/users/create',
                'handler' => [new UserController(), 'create'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/users',
                'handler' => [new UserController(), 'store'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/users/{id}/edit',
                'handler' => [new UserController(), 'edit'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/users/{id}',
                'handler' => [new UserController(), 'update'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/users/{id}/delete',
                'handler' => [new UserController(), 'delete'],
                'middleware' => [$adminMiddleware]
            ],

            // Roles
            [
                'method' => 'GET',
                'path' => '/admin/roles',
                'handler' => [new RoleController(), 'index'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/roles/create',
                'handler' => [new RoleController(), 'create'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/roles',
                'handler' => [new RoleController(), 'store'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/roles/{id}/edit',
                'handler' => [new RoleController(), 'edit'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/roles/{id}',
                'handler' => [new RoleController(), 'update'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/roles/{id}/delete',
                'handler' => [new RoleController(), 'delete'],
                'middleware' => [$adminMiddleware]
            ],

            // Permissions
            [
                'method' => 'GET',
                'path' => '/admin/permissions',
                'handler' => [new PermissionController(), 'index'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/permissions/create',
                'handler' => [new PermissionController(), 'create'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/permissions',
                'handler' => [new PermissionController(), 'store'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/permissions/{id}/edit',
                'handler' => [new PermissionController(), 'edit'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/permissions/{id}',
                'handler' => [new PermissionController(), 'update'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/permissions/{id}/delete',
                'handler' => [new PermissionController(), 'delete'],
                'middleware' => [$adminMiddleware]
            ],

            // Modules
            [
                'method' => 'GET',
                'path' => '/admin/modules',
                'handler' => [new ModuleController(), 'index'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/modules/create',
                'handler' => [new ModuleController(), 'create'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/modules',
                'handler' => [new ModuleController(), 'store'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'GET',
                'path' => '/admin/modules/{id}/edit',
                'handler' => [new ModuleController(), 'edit'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/modules/{id}',
                'handler' => [new ModuleController(), 'update'],
                'middleware' => [$adminMiddleware]
            ],
            [
                'method' => 'POST',
                'path' => '/admin/modules/{id}/delete',
                'handler' => [new ModuleController(), 'delete'],
                'middleware' => [$adminMiddleware]
            ],
        ];
    }
}
