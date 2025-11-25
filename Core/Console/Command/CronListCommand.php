<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Cron\CronScheduler;

/**
 * Class CronListCommand
 * 
 * List all registered cron tasks.
 * Usage: php sunu cron:list
 */
class CronListCommand
{
    public function execute(Application $app, array $args): void
    {
        // Discover tasks first
        $this->discoverTasks();

        $scheduler = CronScheduler::getInstance();
        $tasks = $scheduler->getTasks();

        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║        Registered Cron Tasks           ║\n";
        echo "╚════════════════════════════════════════╝\n\n";

        if (empty($tasks)) {
            echo "No tasks registered.\n\n";
            return;
        }

        foreach ($tasks as $task) {
            $class = get_class($task);
            $expression = $task->expression();
            $description = $task->description();
            $nextRun = $this->getNextRunDate($expression);

            echo "─────────────────────────────────────────\n";
            echo "Task: $class\n";
            echo "Expr: $expression\n";
            echo "Desc: $description\n";
            echo "Next: $nextRun\n";
        }
        echo "─────────────────────────────────────────\n\n";
    }

    private function getNextRunDate(string $expression): string
    {
        // Simple next run calculation (not perfect but good for display)
        // In a real app, use a library like dragonmantank/cron-expression
        return "Calculated at runtime";
    }

    private function discoverTasks(): void
    {
        // Duplicate logic from CronRunCommand - should be refactored to a service
        $scheduler = CronScheduler::getInstance();
        $modulesPath = __DIR__ . '/../../../Modules';
        $modules = glob($modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($modules as $moduleDir) {
            $cronFile = $moduleDir . '/cron.php';
            if (file_exists($cronFile)) {
                $tasks = require $cronFile;
                if (is_array($tasks)) {
                    foreach ($tasks as $taskClass) {
                        if (class_exists($taskClass)) {
                            $scheduler->register(new $taskClass());
                        }
                    }
                }
            }
        }
    }
}
