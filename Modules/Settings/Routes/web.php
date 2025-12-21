<?php

use Modules\Settings\Controllers\HealthCheckController;
use Modules\Settings\Controllers\LogsController;

/*
|--------------------------------------------------------------------------
| Settings Module Routes
|--------------------------------------------------------------------------
*/


// Settings main page (nommée)
$router->get('/admin/settings', [\Modules\Settings\Controllers\SettingsController::class, 'index'])->name('admin.settings.index');

// Health Check / System Monitoring Routes
$router->group(['prefix' => '/admin'], function ($router) {
    // Dashboard de monitoring (accessible aux admins)
    $router->get('/health', [HealthCheckController::class, 'index']);

    // Log viewer routes
    $router->get('/logs/cron', [LogsController::class, 'cronLogs']);
    $router->get('/logs/health', [LogsController::class, 'healthLogs']);
    $router->get('/logs/sms-queue', [LogsController::class, 'smsQueueLogs']);
    $router->post('/logs/clear', [LogsController::class, 'clearLog']);
});

// API publique de monitoring (pas d'auth pour monitoring externe)
$router->group(['prefix' => '/api'], function ($router) {
    // Endpoint JSON pour monitoring externe (UptimeRobot, etc.)
    $router->get('/health', [HealthCheckController::class, 'apiCheck']);

    // Badge SVG pour afficher dans README
    $router->get('/health/badge', [HealthCheckController::class, 'badge']);
});
