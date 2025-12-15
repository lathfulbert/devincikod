<?php

use Modules\ApiKeys\Controllers\ApiKeyController;
use Modules\ApiKeys\Controllers\ApiAnalyticsController;
use Modules\ApiKeys\Controllers\ApiMonitoringController;
use Modules\ApiKeys\Controllers\UserApiKeyController;

/** @var \App\Core\Routing\Router $router */

// User Personal API Key (accessible to all authenticated users)
$router->get('/admin/apikeys', [UserApiKeyController::class, 'index']);
$router->post('/admin/apikeys/generate', [UserApiKeyController::class, 'generate']);
$router->post('/admin/apikeys/revoke', [UserApiKeyController::class, 'revoke']);

// System API Keys Management (Admin - full history and management)
$router->group(['prefix' => '/admin/system-api-keys', 'middleware' => ['auth']], function ($router) {
    $router->get('', [ApiKeyController::class, 'index']);
    $router->get('/create', [ApiKeyController::class, 'create']);
    $router->post('/store', [ApiKeyController::class, 'store']);
    $router->post('/{id}/revoke', [ApiKeyController::class, 'revoke']);
    $router->post('/{id}/activate', [ApiKeyController::class, 'activate']);
    $router->post('/{id}/deactivate', [ApiKeyController::class, 'deactivate']);
    $router->post('/{id}/delete', [ApiKeyController::class, 'destroy']);

    // Analytics
    $router->get('/analytics', [ApiAnalyticsController::class, 'index']);

    // Monitoring
    $router->get('/monitoring', [ApiMonitoringController::class, 'index']);
});
