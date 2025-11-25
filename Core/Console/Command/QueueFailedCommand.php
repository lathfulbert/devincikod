<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Database\Database;

/**
 * Class QueueFailedCommand
 * 
 * List all failed jobs from the queue.
 * Usage: php sunu queue:failed
 */
class QueueFailedCommand
{
    public function execute(Application $app, array $args): void
    {
        $db = Database::getInstance();

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║        Failed Jobs                     ║\n";
        echo "╚════════════════════════════════════════╝\n\n";

        $sql = "SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 50";
        $result = $db->query($sql);
        $failedJobs = $result->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($failedJobs)) {
            echo "✅ No failed jobs found.\n\n";
            return;
        }

        echo "Total: " . count($failedJobs) . " failed job(s)\n\n";

        foreach ($failedJobs as $job) {
            $payload = json_decode($job['payload'], true);
            $jobClass = $payload['job'] ?? 'Unknown';
            $shortClass = basename(str_replace('\\', '/', $jobClass));

            echo "─────────────────────────────────────────\n";
            echo "ID: #{$job['id']}\n";
            echo "Queue: {$job['queue']}\n";
            echo "Job: {$shortClass}\n";
            echo "Failed: {$job['failed_at']}\n";
            echo "Error: " . substr($job['exception'], 0, 100) . "...\n";
        }

        echo "─────────────────────────────────────────\n\n";
        echo "To retry a failed job: php sunu queue:retry <id>\n";
        echo "To retry all: php sunu queue:retry --all\n\n";
    }
}
