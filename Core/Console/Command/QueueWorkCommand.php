<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Queue\QueueWorker;

/**
 * Class QueueWorkCommand
 * 
 * CLI command to start a queue worker.
 * Usage: php sunu queue:work [queue] [--max-jobs=N]
 */
class QueueWorkCommand
{
    public function execute(Application $app, array $args): void
    {
        // Parse arguments
        $queue = $args[0] ?? 'default';
        $maxJobs = null;

        // Check for --max-jobs flag
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--max-jobs=')) {
                $maxJobs = (int) substr($arg, strlen('--max-jobs='));
            }
        }

        // Create and start worker
        $worker = new QueueWorker($app);

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║     Queue Worker Starting...           ║\n";
        echo "╚════════════════════════════════════════╝\n";
        echo "\n";
        echo "Queue: $queue\n";
        if ($maxJobs) {
            echo "Max Jobs: $maxJobs\n";
        } else {
            echo "Max Jobs: unlimited (daemon mode)\n";
        }
        echo "\n";

        try {
            $worker->daemon($queue, $maxJobs);
        } catch (\Throwable $e) {
            echo "\n❌ Worker crashed: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
            exit(1);
        }
    }
}
