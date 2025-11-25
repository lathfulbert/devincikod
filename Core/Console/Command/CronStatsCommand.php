<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Database\Database;

/**
 * Class CronStatsCommand
 * 
 * Display statistics about cron task executions.
 * Usage: php sunu cron:stats [--limit=20]
 */
class CronStatsCommand
{
    public function execute(Application $app, array $args): void
    {
        $limit = 20;

        // Parse --limit flag
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--limit=')) {
                $limit = (int)substr($arg, 8);
            }
        }

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║        Cron Task Statistics            ║\n";
        echo "╚════════════════════════════════════════╝\n\n";

        $db = Database::getInstance();

        // Overall stats
        $this->showOverallStats($db);

        // Recent executions
        $this->showRecentExecutions($db, $limit);

        // Task breakdown
        $this->showTaskBreakdown($db);
    }

    private function showOverallStats(Database $db): void
    {
        echo "Overall Statistics:\n";
        echo "─────────────────────────────────────────\n";

        $stats = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM cron_logs
        ")->fetch();

        if (!$stats || $stats['total'] == 0) {
            echo "No executions recorded yet.\n\n";
            return;
        }

        $successRate = round(($stats['successful'] / $stats['total']) * 100, 1);

        echo "  Total Executions: {$stats['total']}\n";
        echo "  Successful: {$stats['successful']}\n";
        echo "  Failed: {$stats['failed']}\n";
        echo "  Success Rate: {$successRate}%\n\n";
    }

    private function showRecentExecutions(Database $db, int $limit): void
    {
        echo "Recent Executions (last $limit):\n";
        echo "─────────────────────────────────────────\n";

        // Note: We select task_id and join with cron_tasks to get task_class
        // since cron_logs doesn't have task_class directly
        $logs = $db->query("
            SELECT cl.*, ct.task_class
            FROM cron_logs cl
            JOIN cron_tasks ct ON cl.task_id = ct.id
            ORDER BY cl.created_at DESC
            LIMIT ?
        ", [$limit])->fetchAll();

        if (empty($logs)) {
            echo "No executions found.\n\n";
            return;
        }

        foreach ($logs as $log) {
            $icon = $log['status'] === 'success' ? '✅' : '❌';
            $shortClass = basename(str_replace('\\', '/', $log['task_class']));

            echo "  $icon $shortClass - {$log['status']} - {$log['created_at']}\n";

            if ($log['status'] === 'failed' && !empty($log['error'])) {
                $error = substr($log['error'], 0, 80);
                echo "     Error: $error\n";
            }
        }
        echo "\n";
    }

    private function showTaskBreakdown(Database $db): void
    {
        echo "Task Breakdown:\n";
        echo "─────────────────────────────────────────\n";

        $breakdown = $db->query("
            SELECT 
                ct.task_class,
                COUNT(*) as executions,
                SUM(CASE WHEN cl.status = 'success' THEN 1 ELSE 0 END) as successful,
                MAX(cl.created_at) as last_run
            FROM cron_logs cl
            JOIN cron_tasks ct ON cl.task_id = ct.id
            GROUP BY ct.task_class
            ORDER BY executions DESC
        ")->fetchAll();

        if (empty($breakdown)) {
            echo "No data available.\n\n";
            return;
        }

        foreach ($breakdown as $task) {
            $shortClass = basename(str_replace('\\', '/', $task['task_class']));
            $successRate = round(($task['successful'] / $task['executions']) * 100, 1);

            echo "  $shortClass:\n";
            echo "    Executions: {$task['executions']}\n";
            echo "    Success Rate: {$successRate}%\n";
            echo "    Last Run: {$task['last_run']}\n\n";
        }
    }
}
