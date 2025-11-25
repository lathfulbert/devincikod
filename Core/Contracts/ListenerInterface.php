<?php

namespace App\Core\Contracts;

/**
 * Listener Interface
 * 
 * All event listeners must implement this interface.
 */
interface ListenerInterface
{
    /**
     * Handle the event
     * 
     * @param object $event The event instance
     * @return void
     */
    public function handle(object $event): void;
}
