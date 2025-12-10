<?php

use Modules\Admin\Controllers\AdminController;
use Modules\Admin\Controllers\MonitoringController;
use Modules\Admin\Controllers\ModuleController;
use Modules\Admin\Controllers\CacheController;

use Modules\Admin\Controllers\QueueController;
use Modules\Admin\Controllers\CronController;

/** @var \App\Core\Routing\Router $router */

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Dashboard and base routes
$router->get('/admin', [AdminController::class, 'index'])
    ->middleware('can:admin.access'); // Root route
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('can:admin.access');

// Monitoring routes
$router->get('/admin/monitoring', [MonitoringController::class, 'index'])
    ->middleware('can:admin.access')
    ->name('admin.monitoring.index');
$router->post('/admin/monitoring/clear', [MonitoringController::class, 'clear'])
    ->middleware('can:admin.settings.edit')
    ->name('admin.monitoring.clear');

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

// Cache Management
$router->get('/admin/cache', [CacheController::class, 'index'])
    ->middleware('can:admin.settings.view')
    ->name('admin.cache.index');
$router->get('/admin/cache/stats', [CacheController::class, 'stats'])
    ->middleware('can:admin.settings.view')
    ->name('admin.cache.stats');
$router->post('/admin/cache/update', [CacheController::class, 'update'])
    ->middleware('can:admin.settings.edit')
    ->name('admin.cache.update');
$router->post('/admin/cache/test-driver', [CacheController::class, 'testDriver'])
    ->middleware('can:admin.settings.view')
    ->name('admin.cache.test-driver');
$router->post('/admin/cache/clear', [CacheController::class, 'clear'])
    ->middleware('can:admin.settings.edit')
    ->name('admin.cache.clear');

// Queue Management
$router->group([
    'prefix' => '/admin/queue',
    'middleware' => ['auth']
], function ($router) {
    // Queue Dashboard
    $router->get('', [QueueController::class, 'index'])
        ->middleware('can:queue.view')
        ->name('admin.queue.index');

    // Active Jobs
    $router->get('/jobs', [QueueController::class, 'jobs'])
        ->middleware('can:queue.view')
        ->name('admin.queue.jobs');

    // Failed Jobs
    $router->get('/failed', [QueueController::class, 'failed'])
        ->middleware('can:queue.view')
        ->name('admin.queue.failed');

    // Statistics
    $router->get('/stats', [QueueController::class, 'stats'])
        ->middleware('can:queue.view')
        ->name('admin.queue.stats');

    // Retry Failed Job
    $router->post('/retry', [QueueController::class, 'retry'])
        ->middleware('can:queue.manage');

    // Retry All Failed Jobs
    $router->post('/retry-all', [QueueController::class, 'retryAll'])
        ->middleware('can:queue.manage');

    // Delete Failed Job
    $router->post('/delete', [QueueController::class, 'delete'])
        ->middleware('can:queue.manage');
});

// Cron Management
$router->group([
    'prefix' => '/admin/cron',
    'middleware' => ['auth']
], function ($router) {
    // Tasks List
    $router->get('', [CronController::class, 'index'])
        ->middleware('can:cron.view')
        ->name('admin.cron.index');

    // Execution Logs
    $router->get('/logs', [CronController::class, 'logs'])
        ->middleware('can:cron.view')
        ->name('admin.cron.logs');

    // Statistics
    $router->get('/stats', [CronController::class, 'stats'])
        ->middleware('can:cron.view')
        ->name('admin.cron.stats');

    // Toggle Task (Enable/Disable)
    $router->post('/toggle', [CronController::class, 'toggle'])
        ->middleware('can:cron.manage');

    // Run Task Manually
    $router->post('/run-manually', [CronController::class, 'runManually'])
        ->middleware('can:cron.execute');
});
