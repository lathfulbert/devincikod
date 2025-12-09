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
        $gateway = $options['gateway'] ?? 'auto';

        foreach ($recipients as $recipient) {
            try {
                SmsQueue::create([
                    'campaign_id' => $campaignId,
                    'recipient' => $recipient,
                    'message' => $message,
                    'sender_id' => $sender,
                    'gateway' => $gateway,
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

        // Get pending SMS from queue (both unscheduled and scheduled that are due)
        $db = \App\Core\Database\Database::getInstance()->getPdo();
        $now = date('Y-m-d H:i:s');

        $stmt = $db->prepare("
            SELECT * FROM sms_queue
            WHERE status = 'pending'
            AND (scheduled_at IS NULL OR scheduled_at <= ?)
            ORDER BY scheduled_at ASC, created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$now, $batchSize]);
        $pendingData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Convert to model instances
        $pendingSms = [];
        foreach ($pendingData as $data) {
            $sms = new SmsQueue();
            foreach ($data as $key => $value) {
                $sms->$key = $value;
            }
            $pendingSms[] = $sms;
        }

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

            // Update campaign stats if this SMS belongs to a campaign
            if (!empty($sms->campaign_id)) {
                self::updateCampaignStats($sms->campaign_id);
            }

            // Delay between sends to avoid rate limiting
            if ($delay > 0 && $results['processed'] < count($pendingSms)) {
                sleep($delay);
            }
        }

        return $results;
    }

    /**
     * Update campaign statistics and status
     *
     * @param int $campaignId Campaign ID
     * @return void
     */
    public static function updateCampaignStats(int $campaignId): void
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getPdo();

            // Get campaign stats from sms_queue
            $stmt = $db->prepare("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status IN ('pending', 'processing') THEN 1 ELSE 0 END) as pending
                FROM sms_queue
                WHERE campaign_id = ?
            ");
            $stmt->execute([$campaignId]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$stats) {
                return;
            }

            // Update campaign
            $campaign = \Modules\SmsCore\Models\SmsCampaign::find($campaignId);
            if (!$campaign) {
                return;
            }

            $campaign->sent_count = (int) $stats['sent'];
            $campaign->failed_count = (int) $stats['failed'];

            // Determine campaign status
            if ($stats['pending'] == 0) {
                // All messages processed
                if ($campaign->status !== 'completed') {
                    $campaign->markAsCompleted();
                }
            } elseif ($stats['sent'] > 0 || $stats['failed'] > 0) {
                // Some messages processed, some pending
                if ($campaign->status === 'scheduled' || $campaign->status === 'draft') {
                    $campaign->markAsStarted();
                } else {
                    $campaign->save();
                }
            } else {
                // No messages processed yet, just update counts
                $campaign->save();
            }
        } catch (\Exception $e) {
            error_log("Failed to update campaign stats for campaign $campaignId: " . $e->getMessage());
        }
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
        $db = \App\Core\Database\Database::getInstance()->getPdo();

        // Count SMS sent in last minute
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM sms_billing_logs
             WHERE gateway = ? AND created_at >= DATE_SUB(?, INTERVAL 1 MINUTE)");
        $stmt->execute([$gateway->provider_code, $now]);
        $sentLastMinute = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        if ($gateway->rate_limit_per_minute && $sentLastMinute >= $gateway->rate_limit_per_minute) {
            return [
                'allowed' => false,
                'message' => "Per-minute limit reached ({$sentLastMinute}/{$gateway->rate_limit_per_minute})"
            ];
        }

        // Count SMS sent in last hour
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM sms_billing_logs
             WHERE gateway = ? AND created_at >= DATE_SUB(?, INTERVAL 1 HOUR)");
        $stmt->execute([$gateway->provider_code, $now]);
        $sentLastHour = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

        if ($gateway->rate_limit_per_hour && $sentLastHour >= $gateway->rate_limit_per_hour) {
            return [
                'allowed' => false,
                'message' => "Per-hour limit reached ({$sentLastHour}/{$gateway->rate_limit_per_hour})"
            ];
        }

        // Count SMS sent in last day
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM sms_billing_logs
             WHERE gateway = ? AND created_at >= DATE_SUB(?, INTERVAL 1 DAY)");
        $stmt->execute([$gateway->provider_code, $now]);
        $sentLastDay = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

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
