<?php

use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\ApiKeyController;

/** @var \App\Core\Routing\Router $router */

$router->get('/', function () {
    redirect('/login');
});

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout']);

// API Key Management
$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

$router->get('/admin/settings/api', [ApiKeyController::class, 'index'], [$authMiddleware]);
$router->post('/admin/settings/api/generate', [ApiKeyController::class, 'generate'], [$authMiddleware]);
$router->post('/admin/settings/api/regenerate', [ApiKeyController::class, 'regenerate'], [$authMiddleware]);
$router->post('/admin/settings/api/revoke', [ApiKeyController::class, 'revoke'], [$authMiddleware]);
$router->get('/admin/settings/api/docs', [ApiKeyController::class, 'docs'], [$authMiddleware]);
