<?php

use Modules\Dashboard\Controllers\DashboardController;
use Modules\Dashboard\Controllers\WidgetManagerController;

/** @var \App\Core\Routing\Router $router */

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Dashboard route

$router->get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->middleware('module_access:dashboard');

$router->get('/admin/dashboard/widgets', [WidgetManagerController::class, 'index'])
    ->middleware('auth')
    ->middleware('can:manage.widgets');

$router->post('/admin/dashboard/widgets/toggle', [WidgetManagerController::class, 'toggle'])
    ->middleware('auth')
    ->middleware('can:manage.widgets');
