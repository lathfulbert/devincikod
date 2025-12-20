<?php

namespace App\Core\Cron;

use App\Core\Cron\Contracts\CronTaskContract;
use App\Core\Database\Database;

/**
 * Class CronRunner
 * 
 * Executes cron tasks and handles logging.
 */
class CronRunner
{
    private CronScheduler $scheduler;
    private Database $db;
    private bool $loggingEnabled;
    private string $logTable;

    public function __construct()
    {
        $this->scheduler = CronScheduler::getInstance();
        $this->db = Database::getInstance();

        $config = require __DIR__ . '/../../config/cron.php';
        $this->loggingEnabled = $config['logging']['enabled'] ?? true;
        $this->logTable = $config['logging']['table'] ?? 'cron_logs';
    }

    /**
     * Run all due tasks.
     */
    public function run(): void
    {
        $dueTasks = $this->scheduler->getDueTasks();

        if (empty($dueTasks)) {
            // No tasks due
            return;
        }

        echo "Found " . count($dueTasks) . " due task(s).\n";

        foreach ($dueTasks as $task) {
            $this->executeTask($task);
        }

        // Enregistrer le heartbeat après exécution des tâches cron
        \App\Core\Services\HeartbeatHelper::ping('cron_job');
    }

    /**
     * Execute a single task.
     */
    private function executeTask(CronTaskContract $task): void
    {
        $className = get_class($task);

        // Check for overlapping
        if ($this->scheduler->isRunning($task)) {
            echo "Skipping $className (already running)\n";
            return;
        }

        // Acquire lock
        if (!$this->scheduler->acquireLock($task)) {
            echo "Failed to acquire lock for $className\n";
            return;
        }

        echo "Running $className...\n";
        $startTime = microtime(true);

        try {
            // Execute task
            $task->handle();

            $duration = microtime(true) - $startTime;
            echo "✅ $className completed in " . round($duration, 2) . "s\n";

            $this->logSuccess($task, $duration);
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            echo "❌ $className failed: " . $e->getMessage() . "\n";

            $this->logFailure($task, $e, $duration);
        } finally {
            // Release lock
            $this->scheduler->releaseLock($task);
        }
    }

    private function logSuccess(CronTaskContract $task, float $duration): void
    {
        if (!$this->loggingEnabled) return;

        try {
            $taskId = $this->getOrCreateTaskId($task);
            $durationMs = (int)($duration * 1000);

            $sql = "INSERT INTO {$this->logTable} (task_id, started_at, finished_at, status, output, duration_ms)
                    VALUES (?, NOW(), NOW(), ?, ?, ?)";
            $this->db->query($sql, [
                $taskId,
                'success',
                'Completed successfully',
                $durationMs
            ]);

            // Update last_run_at in cron_tasks
            $this->updateTaskRunTime($taskId);
        } catch (\Throwable $e) {
            // Ignore logging errors
            error_log("Failed to log cron success: " . $e->getMessage());
        }
    }

    private function logFailure(CronTaskContract $task, \Throwable $e, float $duration): void
    {
        if (!$this->loggingEnabled) return;

        try {
            $taskId = $this->getOrCreateTaskId($task);
            $durationMs = (int)($duration * 1000);

            $sql = "INSERT INTO {$this->logTable} (task_id, started_at, finished_at, status, error, duration_ms)
                    VALUES (?, NOW(), NOW(), ?, ?, ?)";
            $this->db->query($sql, [
                $taskId,
                'failed',
                $e->getMessage(),
                $durationMs
            ]);
        } catch (\Throwable $e) {
            // Ignore logging errors
            error_log("Failed to log cron failure: " . $e->getMessage());
        }
    }

    /**
     * Get or create task ID from cron_tasks table
     */
    private function getOrCreateTaskId(CronTaskContract $task): int
    {
        $className = get_class($task);

        // Try to find existing task
        $result = $this->db->query("SELECT id FROM cron_tasks WHERE class = ? LIMIT 1", [$className])->fetch();

        if ($result) {
            return (int)$result['id'];
        }

        // Extract module name and task name from class name
        // Example: Modules\SmsCore\Cron\ProcessPendingSmsTask
        $parts = explode('\\', $className);
        $module = $parts[1] ?? 'Core'; // Get module name or default to 'Core'
        $name = end($parts); // Get class name

        // Create new task record
        $this->db->query("
            INSERT INTO cron_tasks (name, module, class, expression, description, enabled, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
        ", [
            $name,
            $module,
            $className,
            $task->expression(),
            $task->description()
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update last_run_at timestamp in cron_tasks
     */
    private function updateTaskRunTime(int $taskId): void
    {
        try {
            $this->db->query("
                UPDATE cron_tasks
                SET last_run_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ", [$taskId]);
        } catch (\Throwable $e) {
            error_log("Failed to update task run time: " . $e->getMessage());
        }
    }
}
