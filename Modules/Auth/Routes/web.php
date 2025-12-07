<?php

/**
 * Auth Module Routes
 */

use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\MfaController;

/** @var \App\Core\Routing\Router $router */

// Auth routes
$router->get('/auth/login', [AuthController::class, 'showLogin']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/logout', [AuthController::class, 'logout']);
$router->get('/logout', [AuthController::class, 'logout']); // Alias
$router->get('/auth/register', [AuthController::class, 'showRegister']);
$router->post('/auth/register', [AuthController::class, 'register']);

// MFA routes
$router->get('/auth/mfa/challenge', [MfaController::class, 'showChallenge']);
$router->post('/auth/mfa/verify', [MfaController::class, 'verifyChallenge']);
$router->post('/auth/mfa/send-otp', [MfaController::class, 'sendOtp']);

// MFA settings (requires login)
$router->get('/auth/mfa/settings', [MfaController::class, 'showSettings']);
$router->post('/auth/mfa/setup', [MfaController::class, 'setup']);
$router->get('/auth/mfa/setup/totp', [MfaController::class, 'showTotpSetup']);
$router->get('/auth/mfa/setup/sms', [MfaController::class, 'showSmsSetup']);
$router->get('/auth/mfa/setup/sms/verify', [MfaController::class, 'showSmsVerification']);
$router->post('/auth/mfa/setup/sms/verify', [MfaController::class, 'verifySmsSetup']);
$router->post('/auth/mfa/setup/totp/verify', [MfaController::class, 'verifyTotpSetup']);
$router->post('/auth/mfa/disable', [MfaController::class, 'disable']);

// Auth logs (admin)
$router->get('/admin/auth/logs', [\Modules\Auth\Controllers\AuthLogsController::class, 'index']);
$router->get('/admin/auth/logs/user', [\Modules\Auth\Controllers\AuthLogsController::class, 'userLogs']);

// SMS OTP Config (admin)
$router->get('/admin/auth/sms-config', [MfaController::class, 'showSmsConfig']);
$router->post('/admin/auth/sms-config', [MfaController::class, 'saveSmsConfig']);
