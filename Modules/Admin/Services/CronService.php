<?php

namespace Modules\Admin\Services;

use App\Core\Cron\CronScheduler;
use App\Core\Database\Database;

/**
 * Class CronService
 * 
 * Service layer for Cron management in Backoffice.
 * Provides data and operations for CronController.
 */
class CronService
{
    private Database $db;
    private CronScheduler $scheduler;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->scheduler = CronScheduler::getInstance();
    }

    /**
     * Get all registered cron tasks.
     */
    public function getAllTasks(): array
    {
        $tasks = $this->scheduler->getTasks();
        $result = [];

        foreach ($tasks as $task) {
            /** @var \App\Core\Cron\Contracts\CronTaskContract $task */
            $taskClass = get_class($task);

            // Get last execution from logs
            // First, find the task_id from cron_tasks
            $taskRecord = $this->db->query("
                SELECT id FROM cron_tasks WHERE class = ? LIMIT 1
            ", [$taskClass])->fetch();

            $lastRun = null;
            if ($taskRecord) {
                $lastRun = $this->db->query("
                    SELECT started_at, status, output
                    FROM cron_logs
                    WHERE task_id = ?
                    ORDER BY started_at DESC
                    LIMIT 1
                ", [$taskRecord['id']])->fetch();
            }

            $result[] = [
                'class' => $taskClass,
                'name' => basename(str_replace('\\', '/', $taskClass)), //AI
                'expression' => $task->expression(),
                'description' => $task->description(),
                'last_run' => $lastRun['started_at'] ?? null,
                'last_status' => $lastRun['status'] ?? null,
                'enabled' => $this->isTaskEnabled($taskClass),
            ];
        }

        return $result;
    }

    /**
     * Get execution logs.
     */
    public function getExecutionLogs(int $page = 1, int $perPage = 20, ?string $taskClass = null): array
    {
        $offset = ($page - 1) * $perPage;

        $whereClause = $taskClass ? "WHERE task_class = ?" : "";
        $params = $taskClass ? [$taskClass, $perPage, $offset] : [$perPage, $offset];

        $logs = $this->db->query("
            SELECT * FROM cron_logs
            {$whereClause}
            ORDER BY started_at DESC
            LIMIT ? OFFSET ?
        ", $params)->fetchAll();

        $countParams = $taskClass ? [$taskClass] : [];
        $total = $this->db->query("
            SELECT COUNT(*) as count FROM cron_logs
            {$whereClause}
        ", $countParams)->fetch();

        return [
            'logs' => $logs,
            'total' => $total['count'],
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total['count'] / $perPage),
        ];
    }

    /**
     * Get statistics for dashboard.
     */
    public function getDashboardStats(): array
    {
        $totalTasks = count($this->scheduler->getTasks());

        // Last 24h executions
        $executions24h = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful
            FROM cron_logs
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->fetch();

        $successRate = $executions24h['total'] > 0
            ? round(($executions24h['successful'] / $executions24h['total']) * 100, 1)
            : 0;

        return [
            'total_tasks' => $totalTasks,
            'executions_24h' => $executions24h['total'] ?? 0,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Get statistics by task.
     */
    public function getTaskStatistics(): array
    {
        return $this->db->query("
            SELECT 
                ct.class as task_class,
                COUNT(*) as executions,
                SUM(CASE WHEN cl.status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN cl.status = 'failed' THEN 1 ELSE 0 END) as failed,
                MAX(cl.started_at) as last_run
            FROM cron_logs cl
            INNER JOIN cron_tasks ct ON cl.task_id = ct.id
            GROUP BY ct.class
            ORDER BY executions DESC
        ")->fetchAll();
    }

    /**
     * Run a task manually.
     */
    public function runTaskManually(string $taskClass): array
    {
        try {
            if (!class_exists($taskClass)) {
                return ['success' => false, 'error' => 'Task class not found'];
            }

            $task = new $taskClass();
            $startTime = microtime(true);

            ob_start();
            $task->handle();
            $output = ob_get_clean();

            $duration = microtime(true) - $startTime;

            // Log execution
            $this->logExecution($taskClass, 'success', $output, $duration);

            return [
                'success' => true,
                'duration' => round($duration, 2),
                'output' => $output,
            ];
        } catch (\Exception $e) {
            $this->logExecution($taskClass, 'failed', $e->getMessage(), 0);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Toggle task enabled/disabled status.
     */
    public function toggleTask(string $taskClass): bool
    {
        // For now, we'll use a simple cache file
        // In production, use database table
        $settingsFile = __DIR__ . '/../../storage/cron_settings.json';

        if (!file_exists(dirname($settingsFile))) {
            mkdir(dirname($settingsFile), 0755, true);
        }

        $settings = file_exists($settingsFile)
            ? json_decode(file_get_contents($settingsFile), true)
            : [];

        $settings[$taskClass] = !($settings[$taskClass] ?? true);

        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

        return $settings[$taskClass];
    }

    /**
     * Check if task is enabled.
     */
    private function isTaskEnabled(string $taskClass): bool
    {
        $settingsFile = __DIR__ . '/../../storage/cron_settings.json';

        if (!file_exists($settingsFile)) {
            return true; // Default enabled
        }

        $settings = json_decode(file_get_contents($settingsFile), true);
        return $settings[$taskClass] ?? true;
    }

    /**
     * Log task execution.
     */
    private function logExecution(string $taskClass, string $status, string $output, float $duration): void
    {
        $this->db->query("
            INSERT INTO cron_logs (task_class, started_at, finished_at, status, output, duration_ms)
            VALUES (?, NOW(), NOW(), ?, ?, ?)
        ", [$taskClass, $status, $output, (int)($duration * 1000)]);
    }
}
