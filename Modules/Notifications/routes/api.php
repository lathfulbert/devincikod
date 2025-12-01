<?php

/**
 * Notification API Routes
 * 
 * All routes secured with API authentication + RBAC permissions
 */

use Modules\Notifications\Controllers\NotificationController;

// Create notification - Requires API auth + send permission
$router->post('/api/v1/notifications', [NotificationController::class, 'create'])
    ->middleware('api_auth')
    ->middleware('can:notifications.send');

// Get notification - Requires API auth + view permission
$router->get('/api/v1/notifications/{id}', [NotificationController::class, 'show'])
    ->middleware('api_auth')
    ->middleware('can:notifications.view');

// Get user notifications - Requires API auth + view permission
$router->get('/api/v1/users/{userId}/notifications', [NotificationController::class, 'userNotifications'])
    ->middleware('api_auth')
    ->middleware('can:notifications.view');

// Test endpoint - Requires API auth + send permission
$router->post('/api/v1/notifications/test', [NotificationController::class, 'test'])
    ->middleware('api_auth')
    ->middleware('can:notifications.send');
