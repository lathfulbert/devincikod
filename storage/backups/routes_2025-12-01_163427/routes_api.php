<?php

/**
 * Notification API Routes
 */

use Modules\Notifications\Controllers\NotificationController;

// Create notification
$router->post('/api/v1/notifications', [NotificationController::class, 'create']);

// Get notification
$router->get('/api/v1/notifications/{id}', [NotificationController::class, 'show']);

// Get user notifications
$router->get('/api/v1/users/{userId}/notifications', [NotificationController::class, 'userNotifications']);

// Test endpoint
$router->post('/api/v1/notifications/test', [NotificationController::class, 'test']);
