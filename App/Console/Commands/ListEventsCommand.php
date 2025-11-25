<?php

namespace App\Console\Commands;

use App\Console\Command;
use App\Core\Events\EventDispatcher;

/**
 * List Events Command
 * 
 * Lists all registered events and their listeners.
 * Usage: php sunu events:list
 */
class ListEventsCommand extends Command
{
    protected string $signature = 'events:list';
    protected string $description = 'List all registered events and listeners';

    public function handle(): int
    {
        $dispatcher = EventDispatcher::getInstance();
        $provider = new \App\Core\Events\ListenerProvider();

        $allListeners = $provider->getAllListeners();

        if (empty($allListeners)) {
            $this->warn('No events registered.');
            $this->info('Events are registered in module events.php files.');
            return 0;
        }

        $this->success('Registered Events:');
        $this->line('');

        foreach ($allListeners as $event => $listeners) {
            // Display event name
            $this->line("📢 <fg=cyan>{$event}</fg=cyan>");

            if (empty($listeners)) {
                $this->line('   <fg=yellow>No listeners</fg=yellow>');
            } else {
                foreach ($listeners as $listener) {
                    // Check if listener is queued
                    $isQueued = false;
                    if (class_exists($listener)) {
                        $interfaces = class_implements($listener);
                        $isQueued = isset($interfaces['App\Core\Contracts\ShouldQueue']);
                    }

                    $queueBadge = $isQueued ? ' <fg=green>[QUEUED]</fg=green>' : ' <fg=blue>[SYNC]</fg=blue>';
                    $this->line("   ↳ {$listener}{$queueBadge}");
                }
            }

            $this->line('');
        }

        $totalEvents = count($allListeners);
        $totalListeners = array_sum(array_map('count', $allListeners));

        $this->info("Total: {$totalEvents} event(s), {$totalListeners} listener(s)");

        return 0;
    }
}
