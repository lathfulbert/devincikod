<?php

namespace Modules\Auth\Listeners;

use App\Core\Contracts\ListenerInterface;
use App\Core\Contracts\ShouldQueue;
use Modules\Auth\Events\UserRegistered;

/**
 * Send Welcome Email Listener
 * 
 * Sends a welcome email to newly registered users.
 * This listener runs asynchronously via the queue.
 */
class SendWelcomeEmail implements ListenerInterface, ShouldQueue
{
    /**
     * Handle the UserRegistered event
     */
    public function handle(object $event): void
    {
        if (!$event instanceof UserRegistered) {
            return;
        }

        $user = $event->getUser();

        // TODO: Implement actual email sending logic
        // For now, just log it
        error_log("Welcome email sent to: " . ($user['email'] ?? 'unknown'));

        // Example: Use your email service
        // EmailService::send($user['email'], 'Welcome!', 'welcome-template', ['user' => $user]);
    }

    /**
     * Get the queue name
     */
    public function queue(): string
    {
        return 'emails';
    }

    /**
     * Get the delay before execution
     */
    public function delay(): int
    {
        return 0; // Send immediately
    }
}
