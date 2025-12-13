<?php

/**
 * Auth Module API Routes
 */

use Modules\Auth\Controllers\AuthApiController;

/** @var \App\Core\Routing\Router $router */

// Auth API endpoints (protected by api middleware)
$router->get('/auth/me', [AuthApiController::class, 'getMe'])
    ->middleware('api')
    ->name('api.auth.me');

$router->get('/auth/me/permissions', [AuthApiController::class, 'getPermissions'])
    ->middleware('api')
    ->name('api.auth.me.permissions');


