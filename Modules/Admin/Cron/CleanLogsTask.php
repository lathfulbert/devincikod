<?php

namespace Modules\Admin\Cron;

use App\Core\Cron\CronTask;
use App\Core\Database\Database;

/**
 * Clean old log entries from cron_logs table.
 * Runs daily at 2 AM.
 */
class CleanLogsTask extends CronTask
{
    protected int $timeout = 600; // 10 minutes

    public function expression(): string
    {
        return '0 2 * * *'; // Daily at 2 AM
    }

    public function description(): string
    {
        return 'Clean old cron logs (keep last 30 days)';
    }

    public function handle(): void
    {
        $db = Database::getInstance();
        $retentionDays = 30;

        echo "Cleaning cron logs older than $retentionDays days...\n";

        $result = $db->query("
            DELETE FROM cron_logs 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ", [$retentionDays]);

        $deleted = $result->rowCount();

        echo "Deleted $deleted old log entries.\n";

        // Log to file for verification
        $logFile = __DIR__ . '/../../../../storage/logs/cleanup.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] Cleaned $deleted cron log entries\n", FILE_APPEND);
    }
}
