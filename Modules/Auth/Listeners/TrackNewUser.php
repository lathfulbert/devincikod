<?php

namespace Modules\Auth\Listeners;

use App\Core\Contracts\ListenerInterface;
use Modules\Auth\Events\UserRegistered;

/**
 * Track New User Listener
 * 
 * Tracks new user registrations for statistics.
 * This listener runs synchronously.
 */
class TrackNewUser implements ListenerInterface
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

        // Update statistics
        // Example: increment user counter, track registration source, etc.
        error_log("New user tracked: " . ($user['email'] ?? 'unknown'));

        // You could store in database:
        // DB::table('statistics')->increment('total_users');
    }
}
