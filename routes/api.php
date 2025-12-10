<?php

/**
 * API Routes
 *
 * These routes are loaded by the Application and are automatically prefixed with '/api'.
 * They are assigned the "api" middleware group by default.
 *
 * Similar to Laravel Sanctum, these routes use token-based authentication.
 */

use App\Core\Routing\Router;

/** @var Router $router */

// API v1 Routes - SMS Module
$router->group(['prefix' => '/v1'], function (Router $router) {

    // SMS Endpoints (protected by api middleware)
    $router->post('/sms/send', [\Modules\SmsCore\Controllers\SmsApiController::class, 'send'])
        ->name('api.sms.send');

    $router->get('/sms/history', [\Modules\SmsCore\Controllers\SmsApiController::class, 'history'])
        ->name('api.sms.history');

    $router->get('/sms/balance', [\Modules\SmsCore\Controllers\SmsApiController::class, 'balance'])
        ->name('api.sms.balance');

    // Sender Names API
    $router->get('/sms/sender-names', [\Modules\SmsCore\Controllers\SenderNameController::class, 'apiGetUserSenderNames'])
        ->name('api.sms.sender-names');
});

// Future API versions can be added here
// $router->group(['prefix' => '/v2'], function (Router $router) {
//     // v2 endpoints
// });
