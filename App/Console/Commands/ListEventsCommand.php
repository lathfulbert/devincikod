<?php

namespace App\Console\Commands;

use App\Core\Application;
use App\Core\Events\EventDispatcher;

/**
 * List Events Command
 * 
 * Lists all registered events and their listeners.
 * Usage: php sunu events:list
 */
class ListEventsCommand
{
    public function execute(Application $app, array $args): void
    {
        $provider = new \App\Core\Events\ListenerProvider();

        $allListeners = $provider->getAllListeners();

        if (empty($allListeners)) {
            echo "\n";
            echo "⚠️  No events registered.\n";
            echo "💡 Events are registered in module events.php files.\n";
            echo "\n";
            return;
        }

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║      Registered Events & Listeners     ║\n";
        echo "╚════════════════════════════════════════╝\n";
        echo "\n";

        foreach ($allListeners as $event => $listeners) {
            // Display event name
            echo "📢 {$event}\n";

            if (empty($listeners)) {
                echo "   ⚠️  No listeners\n";
            } else {
                foreach ($listeners as $listener) {
                    // Check if listener is queued
                    $isQueued = false;
                    if (class_exists($listener)) {
                        $interfaces = class_implements($listener);
                        $isQueued = isset($interfaces['App\Core\Contracts\ShouldQueue']);
                    }

                    $queueBadge = $isQueued ? ' [⚡ QUEUED]' : ' [✓ SYNC]';
                    echo "   ↳ {$listener}{$queueBadge}\n";
                }
            }

            echo "\n";
        }

        $totalEvents = count($allListeners);
        $totalListeners = array_sum(array_map('count', $allListeners));

        echo "───────────────────────────────────────────\n";
        echo "📊 Total: {$totalEvents} event(s), {$totalListeners} listener(s)\n";
        echo "\n";
    }
}
