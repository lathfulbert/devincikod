<?php

namespace App\Jobs;

use App\Core\Queue\Contracts\JobContract;
use Modules\Notifications\Models\NotificationRecipient;
use Modules\Notifications\Models\DeliveryLog;
use App\Core\Notifications\TemplateEngine;
use App\Core\Notifications\ProviderManager;
use App\Core\Notifications\DeliveryTracker;

/**
 * Send Email Notification Job
 * 
 * Queue job for sending email notifications
 */
class SendEmailNotification implements JobContract
{
    protected int $recipientId;
    protected ?string $template;
    protected array $data;
    protected int $attempt = 1;

    public function __construct(int $recipientId, ?string $template, array $data, int $attempt = 1)
    {
        $this->recipientId = $recipientId;
        $this->template = $template;
        $this->data = $data;
        $this->attempt = $attempt;
    }

    /**
     * Handle the job
     */
    public function handle(): void
    {
        try {
            // Get recipient
            $recipient = NotificationRecipient::find($this->recipientId);

            if (!$recipient) {
                throw new \RuntimeException("Recipient {$this->recipientId} not found");
            }

            // Get user
            $user = \Modules\Auth\Models\User::find($recipient->user_id);

            if (!$user || !$user->email) {
                throw new \RuntimeException("User email not found");
            }

            // Initialize services
            $templateEngine = new TemplateEngine();
            $providerManager = new ProviderManager(config('notifications', []));
            $deliveryTracker = new DeliveryTracker();

            // Render template
            $rendered = $templateEngine->render($this->template, 'email', array_merge(
                $this->data,
                ['user' => $user]
            ));

            // Get provider
            $provider = $providerManager->getProvider('email');

            if (!$provider) {
                throw new \RuntimeException("No email provider available");
            }

            // Send email
            $response = $provider->send([
                'to' => $user->email,
                'subject' => $rendered['subject'],
                'body_html' => $rendered['body_html'],
                'body_text' => $rendered['body_text'],
            ]);

            // Log delivery
            $deliveryTracker->logDelivery(
                $this->recipientId,
                'email',
                $provider->getName(),
                $response,
                $this->attempt
            );

            // Update recipient status
            if ($response->isSuccessful()) {
                $recipient->markAsSent();
            } else {
                // Increment attempts
                $recipient->incrementAttempts();

                // Retry if not max attempts
                if ($deliveryTracker->shouldRetry($recipient, 3)) {
                    $this->retry();
                } else {
                    $recipient->markAsFailed();
                }
            }
        } catch (\Exception $e) {
            error_log("Email job failed: " . $e->getMessage());

            // Retry on exception
            if ($this->attempt < 3) {
                $this->retry();
            }
        }
    }

    /**
     * Retry the job
     */
    protected function retry(): void
    {
        $app = app();

        // Re-queue with incremented attempt
        // Re-queue with incremented attempt
        $app->queue->pushDelayed(
            self::class,
            [
                'recipientId' => $this->recipientId,
                'template' => $this->template,
                'data' => $this->data,
                'attempt' => $this->attempt + 1
            ],
            'default',
            $this->getRetryDelay()
        );
    }

    /**
     * Get retry delay in seconds
     */
    protected function getRetryDelay(): int
    {
        // Exponential backoff: 30s, 5m, 30m
        $delays = [30, 300, 1800];
        return $delays[$this->attempt - 1] ?? 1800;
    }
}
