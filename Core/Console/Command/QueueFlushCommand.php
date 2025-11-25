<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Queue\QueueManager;

/**
 * Class QueueFlushCommand
 * 
 * Clear all jobs from a queue.
 * Usage: php sunu queue:flush [queue]
 */
class QueueFlushCommand
{
    public function execute(Application $app, array $args): void
    {
        $queue = $args[0] ?? 'default';

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║        Flush Queue                     ║\n";
        echo "╚════════════════════════════════════════╝\n\n";

        // Get confirmation
        echo "⚠️  WARNING: This will delete ALL jobs from queue '$queue'.\n";
        echo "Are you sure? (yes/no): ";

        $handle = fopen("php://stdin", "r");
        $confirmation = trim(fgets($handle));
        fclose($handle);

        if ($confirmation !== 'yes') {
            echo "\n❌ Flush cancelled.\n\n";
            return;
        }

        $queueManager = QueueManager::getInstance();

        if ($queueManager->clear($queue)) {
            echo "\n✅ Queue '$queue' has been flushed!\n\n";
        } else {
            echo "\n❌ Failed to flush queue '$queue'.\n\n";
            exit(1);
        }
    }
}
