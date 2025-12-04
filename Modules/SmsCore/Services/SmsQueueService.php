<?php

namespace Modules\SmsCore\Services;

use Modules\Settings\Models\Setting;
use Modules\SmsCore\Models\SmsQueue;
use Modules\SmsCore\Models\SmsMessage;
use Modules\Settings\Models\SmsGateway;

class SmsQueueService
{
    /**
     * Determine if queue should be used based on number of recipients
     *
     * @param int $recipientCount Number of recipients
     * @return bool True if queue should be used
     */
    public static function shouldUseQueue(int $recipientCount): bool
    {
        $mode = Setting::get('sms_queue_mode', 'auto');

        // Force modes
        if ($mode === 'always') {
            return true;
        }

        if ($mode === 'never') {
            return false;
        }

        // Auto mode - check threshold
        $threshold = (int) Setting::get('sms_queue_threshold', 100);

        return $recipientCount >= $threshold;
    }

    /**
     * Add multiple SMS to queue
     *
     * @param array $recipients Array of phone numbers
     * @param string $message SMS message content
     * @param string $sender Sender name/ID
     * @param array $options Additional options (campaign_id, user_id, etc.)
     * @return int Number of SMS added to queue
     */
    public static function addToQueue(array $recipients, string $message, string $sender = 'SMS', array $options = []): int
    {
        $added = 0;
        $userId = $options['user_id'] ?? ($_SESSION['user']['id'] ?? null);
        $campaignId = $options['campaign_id'] ?? null;

        foreach ($recipients as $recipient) {
            try {
                SmsQueue::create([
                    'campaign_id' => $campaignId,
                    'recipient' => $recipient,
                    'message' => $message,
                    'sender_id' => $sender,
                    'status' => 'pending',
                    'attempts' => 0,
                    'scheduled_at' => $options['scheduled_at'] ?? null,
                    'created_by' => $userId
                ]);

                $added++;
            } catch (\Exception $e) {
                error_log("Failed to add SMS to queue for $recipient: " . $e->getMessage());
            }
        }

        return $added;
    }

    /**
     * Process pending SMS in queue
     *
     * @param int $batchSize Number of SMS to process in this batch
     * @return array Results ['processed' => int, 'success' => int, 'failed' => int]
     */
    public static function processQueue(int $batchSize = 10): array
    {
        $delay = (int) Setting::get('sms_queue_delay', 1);

        // Get pending SMS from queue
        $pendingSms = SmsQueue::where('status', 'pending')
            ->where(function($query) {
                $query->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', date('Y-m-d H:i:s'));
            })
            ->orderBy('created_at', 'ASC')
            ->limit($batchSize)
            ->get();

        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => []
        ];

        foreach ($pendingSms as $sms) {
            // Mark as processing
            $sms->markAsProcessing();

            try {
                // Get gateway
                $gateway = SmsGateway::getDefault();

                if (!$gateway) {
                    throw new \Exception('No default SMS gateway configured');
                }

                // Check rate limits BEFORE sending
                if ($gateway->rate_limit_enabled) {
                    $canSend = self::checkRateLimits($gateway);

                    if (!$canSend['allowed']) {
                        // Rate limit exceeded - mark back as pending to retry later
                        $sms->update(['status' => 'pending']);
                        $results['skipped']++;
                        $results['details'][] = [
                            'recipient' => $sms->recipient,
                            'status' => 'skipped',
                            'reason' => $canSend['message']
                        ];

                        // Stop processing this batch if rate limit hit
                        echo "  ⚠️  Rate limit reached: {$canSend['message']}\n";
                        break;
                    }
                }

                // Create gateway instance
                $gatewayInstance = SmsGatewayFactory::create($gateway);

                if (!$gatewayInstance) {
                    throw new \Exception('Gateway not implemented: ' . $gateway->provider_code);
                }

                // Initialize services
                $pricingService = new SmsPricingService();
                $billingService = new SmsBillingService();
                $senderService = new SmsSenderService($gatewayInstance, $pricingService, $billingService);

                // Send SMS
                $result = $senderService->send(
                    $sms->recipient,
                    $sms->message,
                    $sms->sender_id,
                    [
                        'user_id' => $sms->created_by,
                        'gateway_name' => $gateway->provider_code
                    ]
                );

                if ($result['success']) {
                    $sms->markAsSent();
                    $results['success']++;
                    $results['details'][] = [
                        'recipient' => $sms->recipient,
                        'status' => 'success',
                        'gateway_id' => $result['gateway_message_id'] ?? null
                    ];
                } else {
                    $sms->markAsFailed($result['message'] ?? 'Unknown error');
                    $results['failed']++;
                    $results['details'][] = [
                        'recipient' => $sms->recipient,
                        'status' => 'failed',
                        'error' => $result['message'] ?? 'Unknown error'
                    ];
                }

            } catch (\Exception $e) {
                $sms->markAsFailed($e->getMessage());
                $results['failed']++;
                $results['details'][] = [
                    'recipient' => $sms->recipient,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }

            $results['processed']++;

            // Delay between sends to avoid rate limiting
            if ($delay > 0 && $results['processed'] < count($pendingSms)) {
                sleep($delay);
            }
        }

        return $results;
    }

    /**
     * Check if gateway rate limits allow sending
     *
     * @param SmsGateway $gateway Gateway to check
     * @return array ['allowed' => bool, 'message' => string]
     */
    private static function checkRateLimits(SmsGateway $gateway): array
    {
        // Query the billing logs to count sent SMS in different time windows
        $now = date('Y-m-d H:i:s');

        // Count SMS sent in last minute
        $sentLastMinute = \App\Core\Database\DB::query(
            "SELECT COUNT(*) as count FROM sms_billing_logs
             WHERE gateway = ? AND created_at >= DATE_SUB(?, INTERVAL 1 MINUTE)",
            [$gateway->provider_code, $now]
        )->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        if ($gateway->rate_limit_per_minute && $sentLastMinute >= $gateway->rate_limit_per_minute) {
            return [
                'allowed' => false,
                'message' => "Per-minute limit reached ({$sentLastMinute}/{$gateway->rate_limit_per_minute})"
            ];
        }

        // Count SMS sent in last hour
        $sentLastHour = \App\Core\Database\DB::query(
            "SELECT COUNT(*) as count FROM sms_billing_logs
             WHERE gateway = ? AND created_at >= DATE_SUB(?, INTERVAL 1 HOUR)",
            [$gateway->provider_code, $now]
        )->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        if ($gateway->rate_limit_per_hour && $sentLastHour >= $gateway->rate_limit_per_hour) {
            return [
                'allowed' => false,
                'message' => "Per-hour limit reached ({$sentLastHour}/{$gateway->rate_limit_per_hour})"
            ];
        }

        // Count SMS sent in last day
        $sentLastDay = \App\Core\Database\DB::query(
            "SELECT COUNT(*) as count FROM sms_billing_logs
             WHERE gateway = ? AND created_at >= DATE_SUB(?, INTERVAL 1 DAY)",
            [$gateway->provider_code, $now]
        )->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        if ($gateway->rate_limit_per_day && $sentLastDay >= $gateway->rate_limit_per_day) {
            return [
                'allowed' => false,
                'message' => "Per-day limit reached ({$sentLastDay}/{$gateway->rate_limit_per_day})"
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Within rate limits'
        ];
    }

    /**
     * Get queue statistics
     *
     * @return array Queue statistics
     */
    public static function getStats(): array
    {
        return [
            'pending' => SmsQueue::where('status', 'pending')->count(),
            'processing' => SmsQueue::where('status', 'processing')->count(),
            'sent' => SmsQueue::where('status', 'sent')->count(),
            'failed' => SmsQueue::where('status', 'failed')->count(),
            'total' => SmsQueue::count(),
        ];
    }

    /**
     * Get pending count
     *
     * @return int Number of pending SMS
     */
    public static function getPendingCount(): int
    {
        return SmsQueue::where('status', 'pending')->count();
    }

    /**
     * Clear old processed items from queue (cleanup)
     *
     * @param int $daysOld Delete items older than X days
     * @return int Number of items deleted
     */
    public static function cleanup(int $daysOld = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysOld days"));

        $deleted = SmsQueue::where('status', 'sent')
            ->where('sent_at', '<', $cutoffDate)
            ->delete();

        return $deleted;
    }

    /**
     * Retry failed SMS
     *
     * @param int $maxAttempts Maximum attempts before giving up
     * @return int Number of SMS reset to pending
     */
    public static function retryFailed(int $maxAttempts = 3): int
    {
        $retried = 0;

        $failedSms = SmsQueue::where('status', 'failed')
            ->where('attempts', '<', $maxAttempts)
            ->get();

        foreach ($failedSms as $sms) {
            $sms->update([
                'status' => 'pending',
                'error_message' => null
            ]);
            $retried++;
        }

        return $retried;
    }
}
