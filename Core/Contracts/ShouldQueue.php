<?php

namespace App\Core\Contracts;

/**
 * Should Queue Interface
 * 
 * Listeners implementing this interface will be executed asynchronously via the queue system.
 */
interface ShouldQueue
{
    /**
     * Get the queue name for this listener
     * 
     * @return string Queue name (e.g., 'default', 'emails', 'notifications')
     */
    public function queue(): string;

    /**
     * Get the delay in seconds before executing
     * 
     * @return int Delay in seconds (0 = immediate)
     */
    public function delay(): int;
}
