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
        $pendingSms = SmsQueue::where('status', 'pending')
            ->where('scheduled_at', '<=', date('Y-m-d H:i:s'))
            ->orderBy('scheduled_at', 'asc')
            ->limit(50)
            ->get();

        if (empty($pendingSms)) {
            return;
        }

        $queueManager = QueueManager::getInstance();
        $processed = 0;

        foreach ($pendingSms as $sms) {
            // Dispatch job to queue
            $queueManager->push(
                SendBulkSmsJob::class,
                ['queueId' => $sms->id],
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
