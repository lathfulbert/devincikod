<?php
namespace Modules\Wallet\Services;

/**
 * Service de facturation du wallet (API, SMS, etc.)
 */
class BillingService
{
    protected WalletService $walletService;
    protected TransactionService $transactionService;
    protected PricingService $pricingService;

    public function __construct(
        WalletService $walletService,
        TransactionService $transactionService,
        PricingService $pricingService
    ) {
        $this->walletService = $walletService;
        $this->transactionService = $transactionService;
        $this->pricingService = $pricingService;
    }

    /**
     * Débite le wallet pour un appel API (facturation automatique par clé API)
     */
    public function chargeApiUsage(int $userId, int $apiKeyId, string $endpoint, array $criteria = []): array
    {
        // Calcul du coût d'un SMS selon les critères (gateway, pays, etc.)
        $baseCost = $this->pricingService->calculateCost($criteria);

        if (!$this->walletService->hasBalance($userId, $baseCost)) {
            throw new \RuntimeException('Solde insuffisant pour appel API');
        }

        $transaction = $this->transactionService->createTransaction(
            $userId,
            'debit',
            $baseCost,
            "API charge: $endpoint",
            [
                'api_key_id' => $apiKeyId,
                'endpoint' => $endpoint,
                'criteria' => $criteria
            ]
        );

        return [
            'charged' => $baseCost,
            'transaction_id' => $transaction['id'],
            'remaining_balance' => $this->walletService->getBalance($userId)
        ];
    }

    public function chargeSms(int $userId, int $count = 1, array $criteria = []): array
    {
        $cost = $this->pricingService->calculateBulkCost($count, $criteria);

        // Check if user has sufficient balance
        if (!$this->walletService->hasBalance($userId, $cost)) {
            throw new \RuntimeException('Insufficient balance');
        }

        // Create debit transaction
        $transaction = $this->transactionService->createTransaction(
            $userId,
            'debit',
            $cost,
            "SMS charge: $count message(s)",
            [
                'sms_count' => $count,
                'unit_price' => $cost / $count,
                'criteria' => $criteria
            ]
        );

        return [
            'charged' => $cost,
            'sms_count' => $count,
            'transaction_id' => $transaction['id'],
            'remaining_balance' => $this->walletService->getBalance($userId)
        ];
    }

    public function refundSms(int $userId, string $transactionId): array
    {
        $original = $this->transactionService->getTransaction($transactionId);

        if (!$original) {
            throw new \RuntimeException('Transaction not found');
        }

        if ($original['type'] !== 'debit') {
            throw new \RuntimeException('Can only refund debit transactions');
        }

        if ($original['status'] !== 'completed') {
            throw new \RuntimeException('Can only refund completed transactions');
        }

        // Create credit transaction (refund)
        $refund = $this->transactionService->createTransaction(
            $userId,
            'credit',
            $original['amount'],
            "Refund for transaction: {$transactionId}",
            [
                'original_transaction' => $transactionId,
                'refund' => true
            ]
        );

        return [
            'refunded' => $original['amount'],
            'refund_transaction_id' => $refund['id'],
            'new_balance' => $this->walletService->getBalance($userId)
        ];
    }

    public function canSendSms(int $userId, int $count = 1, array $criteria = []): bool
    {
        $cost = $this->pricingService->calculateBulkCost($count, $criteria);
        return $this->walletService->hasBalance($userId, $cost);
    }

    public function estimateCost(int $count = 1, array $criteria = []): float
    {
        return $this->pricingService->calculateBulkCost($count, $criteria);
    }
}
