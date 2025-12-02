<?php

use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\ApiKeyController;
use Modules\Auth\Controllers\ProfileController;
use Modules\Auth\Controllers\PasswordResetController;

/** @var \App\Core\Routing\Router $router */

$router->get('/', function () {
    redirect('/login');
});

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout']);

// Password Reset
$router->get('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
$router->post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
$router->get('/reset-password', [PasswordResetController::class, 'resetPassword']);
$router->post('/reset-password', [PasswordResetController::class, 'updatePassword']);

// Protected Routes
$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Profile Management
$router->get('/admin/profile', [ProfileController::class, 'edit'])
    ->middleware('can:auth.profile.view');
$router->get('/admin/profile/change-password', [ProfileController::class, 'changePassword'])
    ->middleware('can:auth.profile.edit');
$router->post('/admin/profile/update-password', [ProfileController::class, 'updatePassword'])
    ->middleware('can:auth.profile.edit');

// API Key Management
$router->get('/admin/api-keys', [ApiKeyController::class, 'index'])
    ->middleware('can:apikeys.view');
$router->post('/admin/api-keys/generate', [ApiKeyController::class, 'generate'])
    ->middleware('can:apikeys.manage');
$router->post('/admin/api-keys/revoke', [ApiKeyController::class, 'revoke'])
    ->middleware('can:apikeys.revoke');
$router->get('/admin/api-keys/docs', [ApiKeyController::class, 'docs'])
    ->middleware('can:apikeys.view');
