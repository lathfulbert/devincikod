<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Cron\CronScheduler;
use App\Core\Cron\CronRunner;

/**
 * Class CronRunTaskCommand
 * 
 * Manually run a specific cron task by class name.
 * Bypasses schedule checking - useful for testing and manual execution.
 * 
 * Usage: php sunu cron:run-task "Modules\Admin\Cron\TestCronTask"
 */
class CronRunTaskCommand
{
    public function execute(Application $app, array $args): void
    {
        $taskClass = $args[0] ?? null;

        if (!$taskClass) {
            echo "❌ Error: Task class name required.\n";
            echo "Usage: php sunu cron:run-task <TaskClassName>\n";
            echo "Example: php sunu cron:run-task \"Modules\\Admin\\Cron\\TestCronTask\"\n\n";
            exit(1);
        }

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║     Manually Running Cron Task         ║\n";
        echo "╚════════════════════════════════════════╝\n\n";

        // Check if class exists
        if (!class_exists($taskClass)) {
            echo "❌ Error: Task class not found: $taskClass\n\n";
            exit(1);
        }

        // Instantiate task
        try {
            $task = new $taskClass();
        } catch (\Throwable $e) {
            echo "❌ Error: Failed to instantiate task: " . $e->getMessage() . "\n\n";
            exit(1);
        }

        // Run the task
        $scheduler = CronScheduler::getInstance();

        // Check for overlap
        if ($scheduler->isRunning($task)) {
            echo "⚠️  Task is already running (locked).\n";
            echo "   If you're sure it's not running, delete the lock file.\n\n";
            exit(1);
        }

        // Acquire lock
        if (!$scheduler->acquireLock($task)) {
            echo "❌ Failed to acquire lock for task.\n\n";
            exit(1);
        }

        echo "Running: $taskClass\n";
        echo "Expression: " . $task->expression() . "\n";
        echo "Description: " . $task->description() . "\n\n";

        $startTime = microtime(true);

        try {
            $task->handle();

            $duration = microtime(true) - $startTime;
            echo "\n✅ Task completed successfully in " . round($duration, 2) . "s\n\n";
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            echo "\n❌ Task failed after " . round($duration, 2) . "s\n";
            echo "Error: " . $e->getMessage() . "\n";
            echo "Trace: " . $e->getTraceAsString() . "\n\n";
        } finally {
            $scheduler->releaseLock($task);
        }
    }
}
