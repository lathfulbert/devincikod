<?php

use Modules\Dashboard\Controllers\DashboardController;

/** @var \App\Core\Routing\Router $router */

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Dashboard route
$router->get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');