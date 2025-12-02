<?php

use Modules\Admin\Controllers\AdminController;
use Modules\Admin\Controllers\MonitoringController;
use Modules\Admin\Controllers\ModuleController;

use Modules\Admin\Controllers\QueueController;
use Modules\Admin\Controllers\CronController;

/** @var \App\Core\Routing\Router $router */

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Dashboard and base routes
$router->get('/admin', [AdminController::class, 'index'])
    ->middleware('can:admin.access'); // Root route
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('can:admin.access');
$router->post('/admin/monitoring/clear', [AdminController::class, 'clearCache'])
    ->middleware('can:admin.settings.edit');

// Module Management
$router->get('/admin/modules', [ModuleController::class, 'index'])
    ->middleware('can:admin.modules.view');
$router->get('/admin/modules/upload', [ModuleController::class, 'upload'])
    ->middleware('can:admin.modules.manage');
$router->post('/admin/modules/process-upload', [ModuleController::class, 'processUpload'])
    ->middleware('can:admin.modules.manage');
$router->post('/admin/modules/enable', [ModuleController::class, 'enable'])
    ->middleware('can:admin.modules.manage');
$router->post('/admin/modules/disable', [ModuleController::class, 'disable'])
    ->middleware('can:admin.modules.manage');
$router->post('/admin/modules/install', [ModuleController::class, 'install'])
    ->middleware('role:admin')
    ->middleware('can:admin.modules.manage');



// Queue Management
$router->get('/admin/queue', [QueueController::class, 'index'])
    ->middleware('can:queue.view');
