<?php

namespace Modules\SmsCore\Cron;

use Modules\SmsCore\Services\SmsQueueService;
use Modules\Settings\Models\Setting;

/**
 * Process SMS Queue Cron Task
 *
 * This task processes pending SMS in the queue
 * Should be run every minute via cron
 *
 * Crontab example:
 * * * * * * php /path/to/sunuframework2/sunu cron:run ProcessSmsQueue
 */
class ProcessSmsQueue
{
    /**
     * Execute the cron task
     *
     * @return void
     */
    public function handle(): void
    {
        $batchSize = (int) Setting::get('sms_queue_batch_size', 10);

        echo "[" . date('Y-m-d H:i:s') . "] Processing SMS Queue...\n";

        try {
            $results = SmsQueueService::processQueue($batchSize);

            echo "  Processed: {$results['processed']}\n";
            echo "  Success: {$results['success']}\n";
            echo "  Failed: {$results['failed']}\n";

            // Show skipped count if any (due to rate limiting)
            if (isset($results['skipped']) && $results['skipped'] > 0) {
                echo "  Skipped (rate limit): {$results['skipped']}\n";
            }

            // Get remaining queue count
            $pending = SmsQueueService::getPendingCount();
            echo "  Pending in queue: $pending\n";

            if ($results['processed'] > 0) {
                echo "✅ Queue processing completed\n";
            } else {
                echo "ℹ️  No SMS to process\n";
            }

        } catch (\Exception $e) {
            echo "❌ Error processing queue: " . $e->getMessage() . "\n";
            error_log("SMS Queue processing error: " . $e->getMessage());
        }

        echo "\n";
    }

    /**
     * Get task schedule
     *
     * @return string Cron schedule expression
     */
    public static function schedule(): string
    {
        // Run every minute
        return '* * * * *';
    }

    /**
     * Get task description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Process pending SMS in queue';
    }
}
