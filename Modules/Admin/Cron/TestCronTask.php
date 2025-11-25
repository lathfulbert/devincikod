<?php

namespace Modules\Admin\Cron;

use App\Core\Cron\CronTask;

class TestCronTask extends CronTask
{
    /**
     * The cron expression (every minute).
     */
    public function expression(): string
    {
        return '* * * * *';
    }

    /**
     * Description of the task.
     */
    public function description(): string
    {
        return 'A simple test task that runs every minute.';
    }

    /**
     * Execute the task.
     */
    public function handle(): void
    {
        // Simulate some work
        sleep(2);

        // Log to a file for verification
        $logFile = __DIR__ . '/../../../../storage/logs/cron_test.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] TestCronTask ran successfully!\n", FILE_APPEND);
    }
}
