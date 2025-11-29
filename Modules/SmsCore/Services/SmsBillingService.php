<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Models\SmsBillingLog;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Services\TransactionService;

class SmsBillingService
{
    protected WalletService $walletService;
    protected TransactionService $transactionService;

    public function __construct()
    {
        $this->walletService = new WalletService();
        $this->transactionService = new TransactionService($this->walletService);
    }

    /**
     * Check if user has enough balance
     */
    public function checkBalance(int $userId, float $amount): bool
    {
        return $this->walletService->hasBalance($userId, $amount);
    }

    /**
     * Process billing for an SMS
     */
    public function processBilling(int $userId, array $pricingData, int $segments, string $senderId, string $recipient, string $gateway, string $type): SmsBillingLog
    {
        $totalCost = $pricingData['unit_cost'] * $segments;

        // 1. Create Billing Log (Pending)
        $log = SmsBillingLog::create([
            'user_id' => $userId,
            'sender_id' => $senderId,
            'recipient' => $recipient,
            'country_code' => $pricingData['country_code'],
            'operator' => $pricingData['operator'],
            'gateway' => $gateway,
            'sms_type' => $type,
            'segments' => $segments,
            'unit_cost' => $pricingData['unit_cost'],
            'total_cost' => $totalCost,
            'currency' => $pricingData['currency'],
            'status' => 'pending'
        ]);

        // 2. Debit Wallet
        try {
            $this->walletService->deductCredit($userId, $totalCost, "SMS to $recipient ($segments segments)");

            // Record Transaction
            $this->transactionService->createTransaction(
                $userId,
                'debit',
                $totalCost,
                "SMS Charge: ID #{$log->id}",
                ['billing_log_id' => $log->id]
            );

            // Update Log Status
            $log->update(['status' => 'paid']);
        } catch (\Exception $e) {
            $log->update(['status' => 'failed']);
            throw $e; // Re-throw to stop sending
        }

        return $log;
    }

    /**
     * Refund a failed SMS
     */
    public function refund(SmsBillingLog $log): void
    {
        if ($log->status !== 'paid') {
            return;
        }

        // Credit Wallet
        $this->walletService->addCredit($log->user_id, $log->total_cost, "Refund SMS #{$log->id}");

        // Record Transaction
        $this->transactionService->createTransaction(
            $log->user_id,
            'credit',
            $log->total_cost,
            "Refund SMS: ID #{$log->id}",
            ['billing_log_id' => $log->id, 'refund' => true]
        );

        $log->update(['status' => 'refunded']);
    }
}
