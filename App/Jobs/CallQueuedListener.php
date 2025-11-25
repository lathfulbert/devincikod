<?php

namespace App\Jobs;

use App\Core\Queue\Job;

/**
 * Call Queued Listener Job
 * 
 * Executes event listeners asynchronously via the queue system.
 */
class CallQueuedListener extends Job
{
    /**
     * Execute the job
     */
    public function handle(): void
    {
        $listenerClass = $this->data['listener'] ?? null;
        $serializedEvent = $this->data['event'] ?? null;

        if (!$listenerClass || !$serializedEvent) {
            throw new \Exception('Invalid queued listener data');
        }

        // Unserialize the event
        $event = unserialize($serializedEvent);

        // Instantiate and call the listener
        $listener = new $listenerClass();

        if (method_exists($listener, 'handle')) {
            $listener->handle($event);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure
        error_log("Queued listener failed: " . $exception->getMessage());

        // You could dispatch a ListenerFailed event here
    }
}
