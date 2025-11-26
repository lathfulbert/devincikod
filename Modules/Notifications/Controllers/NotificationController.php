<?php

namespace Modules\Notifications\Controllers;

use App\Core\Application;
use Modules\Notifications\Services\NotificationService;

/**
 * Notification Controller
 * 
 * API endpoints for notifications
 */
class NotificationController
{
    protected Application $app;
    protected NotificationService $notificationService;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->notificationService = new NotificationService($app);
    }

    /**
     * Create and send notification
     * 
     * POST /api/v1/notifications
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        if (empty($data['event']) || empty($data['user_id'])) {
            return json_encode([
                'success' => false,
                'error' => 'Missing required fields: event, user_id',
            ]);
        }

        try {
            $notification = $this->notificationService->send([
                'event' => $data['event'],
                'user_id' => $data['user_id'],
                'template' => $data['template'] ?? null,
                'channels' => $data['channels'] ?? [],
                'data' => $data['data'] ?? [],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'priority' => $data['priority'] ?? 5,
            ]);

            return json_encode([
                'success' => true,
                'notification_id' => $notification->id,
                'status' => $notification->status,
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get notification details
     * 
     * GET /api/v1/notifications/{id}
     */
    public function show($id)
    {
        $notification = $this->notificationService->getNotification($id);

        if (!$notification) {
            return json_encode([
                'success' => false,
                'error' => 'Notification not found',
            ]);
        }

        return json_encode([
            'success' => true,
            'notification' => [
                'id' => $notification->id,
                'event_type' => $notification->event_type,
                'status' => $notification->status,
                'created_at' => $notification->created_at,
                'scheduled_at' => $notification->scheduled_at,
            ],
        ]);
    }

    /**
     * Get user notifications
     * 
     * GET /api/v1/users/{userId}/notifications
     */
    public function userNotifications($userId)
    {
        $notifications = $this->notificationService->getUserNotifications($userId);

        return json_encode([
            'success' => true,
            'count' => count($notifications),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Test endpoint - send test email
     * 
     * POST /api/v1/notifications/test
     */
    public function test()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $userId = $data['user_id'] ?? 1;
        $template = $data['template'] ?? 'test';

        $success = $this->notificationService->sendEmailNow($userId, $template, [
            'message' => 'This is a test notification from SunuFramework',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return json_encode([
            'success' => $success,
            'message' => $success ? 'Test email sent' : 'Failed to send test email',
        ]);
    }
}
