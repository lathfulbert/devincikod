<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class SmsSenderService
{
    protected SmsGatewayInterface $gateway;
    protected SmsPricingService $pricingService;
    protected SmsBillingService $billingService;

    public function __construct(
        SmsGatewayInterface $gateway,
        SmsPricingService $pricingService,
        SmsBillingService $billingService
    ) {
        $this->gateway = $gateway;
        $this->pricingService = $pricingService;
        $this->billingService = $billingService;
    }

    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        $userId = $options['user_id'] ?? null;
        $gatewayName = $options['gateway_name'] ?? 'unknown';

        // If no user ID, skip billing (system message) or handle as admin
        if (!$userId) {
            return $this->gateway->send($to, $message, $senderId, $options);
        }

        // 1. Calculate Cost
        $pricing = $this->pricingService->calculateCost($to, $gatewayName);
        $segments = $this->pricingService->calculateSegments($message);
        $totalCost = $pricing['unit_cost'] * $segments;

        // 2. Check Balance
        if (!$this->billingService->checkBalance($userId, $totalCost)) {
            return [
                'success' => false,
                'message' => "Insufficient balance. Cost: {$totalCost} {$pricing['currency']}",
                'gateway_response' => null
            ];
        }

        // 3. Process Billing (Debit)
        try {
            $log = $this->billingService->processBilling(
                $userId,
                $pricing,
                $segments,
                $senderId,
                $to,
                $gatewayName,
                'text'
            );
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Billing error: " . $e->getMessage(),
                'gateway_response' => null
            ];
        }

        // 4. Send SMS
        try {
            $result = $this->gateway->send($to, $message, $senderId, $options);

            if (!$result['success']) {
                // 5. Refund on Failure
                $this->billingService->refund($log);
                $log->update(['status' => 'failed']);
            } else {
                // Update log with success info if needed
                // $log->update(['status' => 'paid']); // Already set in processBilling
            }

            return $result;
        } catch (\Exception $e) {
            // 5. Refund on Exception
            $this->billingService->refund($log);
            $log->update(['status' => 'failed']);

            return [
                'success' => false,
                'message' => "Gateway error: " . $e->getMessage(),
                'gateway_response' => null
            ];
        }
    }
}
