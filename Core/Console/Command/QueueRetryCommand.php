<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Queue\Drivers\DatabaseDriver;
use App\Core\Queue\QueueManager;

/**
 * Class QueueRetryCommand
 * 
 * Retry failed jobs.
 * Usage: php sunu queue:retry <id>
 *        php sunu queue:retry --all
 */
class QueueRetryCommand
{
    public function execute(Application $app, array $args): void
    {
        $queueManager = QueueManager::getInstance();
        $driver = $queueManager->getDriver();

        if (!$driver instanceof DatabaseDriver) {
            echo "❌ Retry is only supported for database driver.\n";
            exit(1);
        }

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║        Retry Failed Jobs               ║\n";
        echo "╚════════════════════════════════════════╝\n\n";

        $retryAll = in_array('--all', $args);

        if ($retryAll) {
            $this->retryAll($driver);
        } else {
            $id = $args[0] ?? null;

            if (!$id || !is_numeric($id)) {
                echo "❌ Please provide a job ID or use --all flag.\n";
                echo "Usage: php sunu queue:retry <id>\n";
                echo "       php sunu queue:retry --all\n\n";
                exit(1);
            }

            $this->retryOne($driver, (int)$id);
        }
    }

    private function retryOne(DatabaseDriver $driver, int $id): void
    {
        echo "🔄 Retrying failed job #$id...\n";

        if ($driver->retryFailedJob($id)) {
            echo "✅ Job #$id has been re-queued successfully!\n\n";
        } else {
            echo "❌ Failed to retry job #$id. Job not found or error occurred.\n\n";
            exit(1);
        }
    }

    private function retryAll(DatabaseDriver $driver): void
    {
        $failedJobs = $driver->getFailedJobs(1000);

        if (empty($failedJobs)) {
            echo "✅ No failed jobs to retry.\n\n";
            return;
        }

        $count = count($failedJobs);
        echo "🔄 Retrying $count failed job(s)...\n";

        $retried = 0;
        foreach ($failedJobs as $job) {
            if ($driver->retryFailedJob($job['id'])) {
                $retried++;
                echo "  ✓ Job #{$job['id']} re-queued\n";
            }
        }

        echo "\n✅ Successfully retried $retried/$count job(s)!\n\n";
    }
}
