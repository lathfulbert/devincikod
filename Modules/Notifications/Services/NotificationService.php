<?php

namespace Modules\Notifications\Services;

use App\Core\Application;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationRecipient;
use App\Core\Notifications\TemplateEngine;
use App\Core\Notifications\ProviderManager;
use App\Core\Notifications\NotificationRouter;
use App\Jobs\SendEmailNotification;

/**
 * Notification Service
 * 
 * Main service for creating and sending notifications
 */
class NotificationService
{
    protected Application $app;
    protected TemplateEngine $templateEngine;
    protected ProviderManager $providerManager;
    protected NotificationRouter $router;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $config = config('notifications', []);

        $this->templateEngine = new TemplateEngine();
        $this->providerManager = new ProviderManager($config);
        $this->router = new NotificationRouter();
    }

    /**
     * Send notification
     * 
     * @param array $payload [
     *   'event' => string,
     *   'user_id' => int|int[],
     *   'template' => string,
     *   'channels' => string[], optional
     *   'data' => array,
     *   'scheduled_at' => string|null
     * ]
     */
    public function send(array $payload): Notification
    {
        // Create notification record
        $notification = Notification::create([
            'event_type' => $payload['event'],
            'data' => $payload['data'] ?? [],
            'status' => 'pending',
            'priority' => $payload['priority'] ?? 5,
            'scheduled_at' => $payload['scheduled_at'] ?? null,
        ]);

        // Normalize user IDs
        $userIds = is_array($payload['user_id'] ?? null)
            ? $payload['user_id']
            : [$payload['user_id'] ?? null];

        // Create recipients
        foreach ($userIds as $userId) {
            if (!$userId) continue;

            // Get channels for user
            $requestedChannels = $payload['channels'] ?? [];
            $channels = $this->router->getChannelsForUser($userId, $requestedChannels);

            if (empty($channels)) continue;

            // Create recipient record
            $recipient = NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'channels' => $channels,
                'status' => 'pending',
            ]);

            // If not scheduled, enqueue immediately
            if (!$notification->scheduled_at) {
                $this->enqueueRecipient($recipient, $payload['template'] ?? null, $payload['data'] ?? []);
            }
        }

        return $notification;
    }

    /**
     * Enqueue recipient notification
     */
    protected function enqueueRecipient(NotificationRecipient $recipient, ?string $template, array $data): void
    {
        foreach ($recipient->channels as $channel) {
            if ($channel === 'email') {
                // Enqueue email job
                $this->app->queue->push(new SendEmailNotification(
                    $recipient->id,
                    $template,
                    $data
                ));
            }
            // Add other channels (SMS, Push) in future
        }
    }

    /**
     * Send email directly (bypass queue)
     */
    public function sendEmailNow(int $userId, string $template, array $data): bool
    {
        try {
            // Render template
            $rendered = $this->templateEngine->render($template, 'email', $data);

            // Get user (simplified - you should have User model)
            $user = \Modules\Auth\Models\User::find($userId);

            if (!$user) {
                throw new \RuntimeException("User {$userId} not found");
            }

            // Get provider
            $provider = $this->providerManager->getProvider('email');

            if (!$provider) {
                throw new \RuntimeException("No email provider available");
            }

            // Send
            $response = $provider->send([
                'to' => $user->email,
                'subject' => $rendered['subject'],
                'body_html' => $rendered['body_html'],
                'body_text' => $rendered['body_text'],
            ]);

            return $response->isSuccessful();
        } catch (\Exception $e) {
            error_log("Email send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notification by ID
     */
    public function getNotification(int $id): ?Notification
    {
        return Notification::find($id);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId, int $limit = 20): array
    {
        return NotificationRecipient::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
