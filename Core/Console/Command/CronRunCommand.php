<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Cron\CronRunner;
use App\Core\Cron\CronScheduler;

/**
 * Class CronRunCommand
 * 
 * Run the cron scheduler.
 * This command should be called every minute by the system cron daemon.
 * Usage: php sunu cron:run
 */
class CronRunCommand
{
    public function execute(Application $app, array $args): void
    {
        // 1. Discover tasks (simple implementation for now)
        $this->discoverTasks();

        // 2. Run scheduler
        $runner = new CronRunner();
        $runner->run();
    }

    private function discoverTasks(): void
    {
        $scheduler = CronScheduler::getInstance();

        // TODO: Implement proper module auto-discovery
        // For now, we'll look for a 'cron.php' file in modules

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
