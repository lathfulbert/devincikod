<?php

namespace Modules\SmsCore\Cron;

use App\Core\Cron\CronTask;
use Modules\SmsCore\Models\SmsQueue;
use Modules\SmsCore\Jobs\SendBulkSmsJob;
use App\Core\Queue\QueueManager;

class ProcessPendingSmsTask extends CronTask
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
        return 'Process pending SMS messages in the queue.';
    }

    /**
     * Execute the task.
     */
    public function handle(): void
    {
        // Get pending SMS (limit to 50 per run to avoid overload)
        $db = \App\Core\Database\Database::getInstance()->getPdo();
        $now = date('Y-m-d H:i:s');

        $stmt = $db->prepare("
            SELECT * FROM sms_queue
            WHERE status = 'pending'
            AND (scheduled_at IS NULL OR scheduled_at <= ?)
            ORDER BY scheduled_at ASC, created_at ASC
            LIMIT 50
        ");
        $stmt->execute([$now]);
        $pendingData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($pendingData)) {
            return;
        }

        $queueManager = QueueManager::getInstance();
        $processed = 0;

        foreach ($pendingData as $data) {
            // Dispatch job to queue
            $queueManager->push(
                SendBulkSmsJob::class,
                ['queueId' => $data['id']],
                'default'
            );
            $processed++;
        }

        // Log for verification
        $logFile = __DIR__ . '/../../../storage/logs/sms_cron.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(
            $logFile,
            "[$timestamp] ProcessPendingSmsTask: Dispatched $processed SMS to queue\n",
            FILE_APPEND
        );
    }
}
