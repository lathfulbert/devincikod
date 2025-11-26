<?php

namespace App\Core\Notifications;

use Modules\Notifications\Models\DeliveryLog;
use Modules\Notifications\Models\NotificationRecipient;

/**
 * Delivery Tracker
 * 
 * Tracks notification delivery status and logs
 */
class DeliveryTracker
{
    /**
     * Log delivery attempt
     */
    public function logDelivery(
        int $recipientId,
        string $channel,
        string $provider,
        ProviderResponse $response,
        int $attempt = 1
    ): DeliveryLog {
        return DeliveryLog::create([
            'recipient_id' => $recipientId,
            'channel' => $channel,
            'provider' => $provider,
            'provider_message_id' => $response->messageId,
            'status' => $response->success ? 'sent' : 'failed',
            'error_message' => $response->error,
            'metadata' => $response->metadata,
            'attempt' => $attempt,
        ]);
    }

    /**
     * Update delivery status
     */
    public function updateStatus(int $logId, string $status, ?string $error = null): void
    {
        $log = DeliveryLog::find($logId);

        if ($log) {
            $log->update([
                'status' => $status,
                'error_message' => $error,
            ]);
        }
    }

    /**
     * Get delivery logs for recipient
     */
    public function getLogsForRecipient(int $recipientId): array
    {
        return DeliveryLog::where('recipient_id', $recipientId)
            ->orderBy('sent_at', 'desc')
            ->get();
    }

    /**
     * Get stats for provider
     */
    public function getProviderStats(string $provider, int $days = 7): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $logs = DeliveryLog::where('provider', $provider)
            ->where('sent_at', '>=', $since)
            ->get();

        $total = count($logs);
        $successful = $logs->filter(fn($log) => $log->isSuccessful())->count();
        $failed = $logs->filter(fn($log) => $log->isFailed())->count();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? ($successful / $total) * 100 : 0,
        ];
    }

    /**
     * Check if should retry
     */
    public function shouldRetry(NotificationRecipient $recipient, int $maxAttempts = 3): bool
    {
        return $recipient->attempts < $maxAttempts;
    }
}
