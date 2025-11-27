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
$router->get('/admin', [AdminController::class, 'index'], [$authMiddleware]);
$router->get('/', [AdminController::class, 'index'], [$authMiddleware]); // Root route
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [$authMiddleware]);

// Monitoring Routes
$router->get('/admin/monitoring', [MonitoringController::class, 'index'], [$authMiddleware]);
$router->post('/admin/monitoring/clear', [MonitoringController::class, 'clear'], [$authMiddleware]);

// Module Management
$router->get('/admin/modules', [ModuleController::class, 'index'], [$authMiddleware]);
$router->get('/admin/modules/upload', [ModuleController::class, 'upload'], [$authMiddleware]);
$router->post('/admin/modules/process-upload', [ModuleController::class, 'processUpload'], [$authMiddleware]);
$router->post('/admin/modules/enable', [ModuleController::class, 'enable'], [$authMiddleware]);
$router->post('/admin/modules/disable', [ModuleController::class, 'disable'], [$authMiddleware]);
$router->post('/admin/modules/install', [ModuleController::class, 'install'], [$authMiddleware]);
$router->post('/admin/modules/uninstall', [ModuleController::class, 'uninstall'], [$authMiddleware]);
$router->post('/admin/modules/{name}/delete', [ModuleController::class, 'delete'], [$authMiddleware]);

// Users Management
$router->get('/admin/users', [UserController::class, 'index'], [$authMiddleware]);
$router->get('/admin/users/create', [UserController::class, 'create'], [$authMiddleware]);
$router->post('/admin/users/store', [UserController::class, 'store'], [$authMiddleware]);
$router->get('/admin/users/{id}/edit', [UserController::class, 'edit'], [$authMiddleware]);
$router->post('/admin/users/{id}/update', [UserController::class, 'update'], [$authMiddleware]);
$router->get('/admin/users/{id}/delete', [UserController::class, 'delete'], [$authMiddleware]);
$router->post('/admin/users/{id}/delete', [UserController::class, 'delete'], [$authMiddleware]);

// Roles Management
$router->get('/admin/roles', [RoleController::class, 'index'], [$authMiddleware]);
$router->get('/admin/roles/create', [RoleController::class, 'create'], [$authMiddleware]);
$router->post('/admin/roles/store', [RoleController::class, 'store'], [$authMiddleware]);
$router->get('/admin/roles/{id}/edit', [RoleController::class, 'edit'], [$authMiddleware]);
$router->post('/admin/roles/{id}/update', [RoleController::class, 'update'], [$authMiddleware]);
$router->get('/admin/roles/{id}/delete', [RoleController::class, 'delete'], [$authMiddleware]);
$router->post('/admin/roles/{id}/delete', [RoleController::class, 'delete'], [$authMiddleware]);

// Permissions Management
$router->get('/admin/permissions', [PermissionController::class, 'index'], [$authMiddleware]);
$router->get('/admin/permissions/create', [PermissionController::class, 'create'], [$authMiddleware]);
$router->post('/admin/permissions/store', [PermissionController::class, 'store'], [$authMiddleware]);
$router->get('/admin/permissions/{id}/edit', [PermissionController::class, 'edit'], [$authMiddleware]);
$router->post('/admin/permissions/{id}/update', [PermissionController::class, 'update'], [$authMiddleware]);
$router->get('/admin/permissions/{id}/delete', [PermissionController::class, 'delete'], [$authMiddleware]);
$router->post('/admin/permissions/{id}/delete', [PermissionController::class, 'delete'], [$authMiddleware]);

// Queue Management
$router->get('/admin/queue', [QueueController::class, 'index'], [$authMiddleware]);
$router->get('/admin/queue/jobs', [QueueController::class, 'jobs'], [$authMiddleware]);
$router->get('/admin/queue/failed', [QueueController::class, 'failed'], [$authMiddleware]);
$router->post('/admin/queue/retry', [QueueController::class, 'retry'], [$authMiddleware]);
$router->post('/admin/queue/retry-all', [QueueController::class, 'retryAll'], [$authMiddleware]);
$router->post('/admin/queue/delete', [QueueController::class, 'delete'], [$authMiddleware]);
$router->get('/admin/queue/stats', [QueueController::class, 'stats'], [$authMiddleware]);
$router->get('/admin/queue/api/stats', [QueueController::class, 'apiStats'], [$authMiddleware]);

// Cron Management
$router->get('/admin/cron', [CronController::class, 'index'], [$authMiddleware]);
$router->post('/admin/cron/toggle', [CronController::class, 'toggle'], [$authMiddleware]);
$router->post('/admin/cron/run', [CronController::class, 'runManually'], [$authMiddleware]);
$router->get('/admin/cron/logs', [CronController::class, 'logs'], [$authMiddleware]);
$router->get('/admin/cron/stats', [CronController::class, 'stats'], [$authMiddleware]);
