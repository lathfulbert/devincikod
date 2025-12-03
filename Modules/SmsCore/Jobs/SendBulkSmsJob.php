<?php

namespace Modules\SmsCore\Jobs;

use App\Core\Queue\Job;
use Modules\SmsCore\Models\SmsCampaign;
use Modules\SmsCore\Models\SmsQueue;
use Modules\Settings\Models\SmsGateway;
use Modules\SmsCore\Services\SmsGatewayFactory;
use Modules\SmsCore\Services\SmsPricingService;
use Modules\SmsCore\Services\SmsBillingService;
use Modules\SmsCore\Services\SmsSenderService;

class SendBulkSmsJob extends Job
{
    protected int $queueId;

    public function __construct()
    {
        // Constructor sans paramètres pour le JobSerializer
    }

    public function setData(array $data): Job
    {
        parent::setData($data);
        $this->queueId = $data['queueId'] ?? 0;
        return $this;
    }

    public function handle(): void
    {
        // Get queue item
        $queueItem = SmsQueue::find($this->queueId);

        if (!$queueItem) {
            return;
        }

        // Mark as processing
        $queueItem->update(['status' => 'processing']);

        try {
            // Get default gateway
            $gatewayConfig = SmsGateway::getDefault();

            if (!$gatewayConfig) {
                throw new \Exception('No default SMS gateway configured');
            }

            // Create gateway instance
            $gateway = SmsGatewayFactory::create($gatewayConfig);

            if (!$gateway) {
                throw new \Exception('Failed to create gateway instance');
            }

            // Initialize services for billing
            $pricingService = new SmsPricingService();
            $billingService = new SmsBillingService();
            $senderService = new SmsSenderService($gateway, $pricingService, $billingService);

            // Send SMS
            $result = $senderService->send(
                $queueItem->recipient,
                $queueItem->message,
                $queueItem->sender_id,
                [
                    'user_id' => null,
                    'gateway_name' => $gatewayConfig->provider_code,
                    'campaign_id' => $queueItem->campaign_id
                ]
            );

            if ($result['success']) {
                // Mark as sent
                $queueItem->update([
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);

                // Update campaign stats
                if ($queueItem->campaign_id) {
                    $campaign = SmsCampaign::find($queueItem->campaign_id);
                    if ($campaign) {
                        $campaign->update([
                            'sent_count' => $campaign->sent_count + 1
                        ]);
                    }
                }
            } else {
                throw new \Exception($result['message'] ?? 'Unknown error');
            }

        } catch (\Exception $e) {
            // Mark as failed
            $attempts = $queueItem->attempts + 1;
            $queueItem->update([
                'status' => $attempts >= 3 ? 'failed' : 'pending', // Retry up to 3 times
                'attempts' => $attempts,
                'error_message' => $e->getMessage()
            ]);

            // Update campaign stats
            if ($queueItem->campaign_id && $attempts >= 3) {
                $campaign = SmsCampaign::find($queueItem->campaign_id);
                if ($campaign) {
                    $campaign->update([
                        'failed_count' => $campaign->failed_count + 1
                    ]);
                }
            }

            throw $e; // Re-throw for job retry mechanism
        }
    }
}
