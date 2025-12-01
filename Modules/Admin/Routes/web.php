<?php

use Modules\Admin\Controllers\AdminController;
use Modules\Admin\Controllers\MonitoringController;
use Modules\Admin\Controllers\ModuleController;
use Modules\Admin\Controllers\UserController;
use Modules\Admin\Controllers\RoleController;
use Modules\Admin\Controllers\PermissionController;
use Modules\Admin\Controllers\QueueController;
use Modules\Admin\Controllers\CronController;

/** @var \App\Core\Routing\Router $router */

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Dashboard and base routes
$router->get('/admin', [AdminController::class, 'index'])
    ->middleware('can:admin.access');
$router->get('/', [AdminController::class, 'index'], [$authMiddleware]); // Root route
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('can:admin.access');

// Monitoring Routes
$router->get('/admin/monitoring', [MonitoringController::class, 'index'], [$authMiddleware]);
$router->post('/admin/monitoring/clear', [MonitoringController::class, 'clear'], [$authMiddleware]);

// Module Management
$router->get('/admin/modules', [ModuleController::class, 'index'], [$authMiddleware]);
$router->get('/admin/modules/upload', [ModuleController::class, 'upload'], [$authMiddleware]);
$router->post('/admin/modules/process-upload', [ModuleController::class, 'processUpload'], [$authMiddleware]);
$router->post('/admin/modules/enable', [ModuleController::class, 'enable'], [$authMiddleware]);
$router->post('/admin/modules/disable', [ModuleController::class, 'disable'], [$authMiddleware]);
$router->post('/admin/modules/install', [ModuleController::class, 'install'])
    ->middleware('role:admin')
    ->middleware('can:admin.modules.manage');
$router->post('/admin/modules/uninstall', [ModuleController::class, 'uninstall'])
    ->middleware('role:admin')
    ->middleware('can:admin.modules.manage');
$router->post('/admin/modules/{name}/delete', [ModuleController::class, 'delete'], [$authMiddleware]);

// Users Management
$router->get('/admin/users', [UserController::class, 'index'])
    ->middleware('can:admin.users.view');
$router->get('/admin/users/create', [UserController::class, 'create'], [$authMiddleware]);
$router->post('/admin/users/store', [UserController::class, 'store'])
    ->middleware('can:admin.users.create');
$router->get('/admin/users/{id}/edit', [UserController::class, 'edit'], [$authMiddleware]);
$router->post('/admin/users/{id}/update', [UserController::class, 'update'], [$authMiddleware]);
$router->get('/admin/users/{id}/delete', [UserController::class, 'delete'], [$authMiddleware]);
$router->post('/admin/users/{id}/delete', [UserController::class, 'delete'])
    ->middleware('can:admin.users.delete');

// Roles Management
$router->get('/admin/roles', [RoleController::class, 'index'])
    ->middleware('can:admin.roles.view');
$router->post('/admin/permissions/{id}/update', [PermissionController::class, 'update'])
    ->middleware('role:admin');
$router->get('/admin/permissions/{id}/delete', [PermissionController::class, 'delete'], [$authMiddleware]);
$router->post('/admin/permissions/{id}/delete', [PermissionController::class, 'delete'], [$authMiddleware]);

// Queue Management
$router->get('/admin/queue', [QueueController::class, 'index'])
    ->middleware('can:queue.view');
