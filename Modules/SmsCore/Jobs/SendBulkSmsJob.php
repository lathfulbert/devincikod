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
use Modules\SmsCore\Services\SmsQueueService;

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
                $queueItem->markAsSent();

                // Update campaign stats using centralized method
                if ($queueItem->campaign_id) {
                    SmsQueueService::updateCampaignStats($queueItem->campaign_id);
                }
            } else {
                throw new \Exception($result['message'] ?? 'Unknown error');
            }

        } catch (\Exception $e) {
            // Mark as failed
            $queueItem->markAsFailed($e->getMessage());

            // Update campaign stats using centralized method
            if ($queueItem->campaign_id) {
                SmsQueueService::updateCampaignStats($queueItem->campaign_id);
            }

            // Only throw if we haven't exceeded max attempts (for retry)
            if ($queueItem->attempts < 3) {
                throw $e;
            }
        }
    }
}
