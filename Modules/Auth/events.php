<?php

/**
 * Auth Module Event Configuration
 * 
 * Register event listeners for the Auth module.
 * 
 * Format:
 * [
 *     EventClass::class => [
 *         ListenerClass::class,
 *         AnotherListenerClass::class,
 *     ],
 * ]
 */

return [
    \Modules\Auth\Events\UserRegistered::class => [
        \Modules\Auth\Listeners\SendWelcomeEmail::class,    // Async (queued)
        \Modules\Auth\Listeners\TrackNewUser::class,        // Sync
    ],
];
