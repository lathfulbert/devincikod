<?php

namespace Modules\Wallet\Services;

class TransactionService
{
    protected array $transactions = [];
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function createTransaction(
        int $userId,
        string $type,
        float $amount,
        string $description = '',
        array $metadata = []
    ): array {
        $transaction = [
            'id' => uniqid('txn_'),
            'user_id' => $userId,
            'type' => $type, // credit, debit, refund
            'amount' => $amount,
            'description' => $description,
            'metadata' => $metadata,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->transactions[] = $transaction;

        // Execute transaction
        try {
            if ($type === 'credit') {
                $this->walletService->addCredit($userId, $amount, $description);
            } elseif ($type === 'debit') {
                $this->walletService->deductCredit($userId, $amount, $description);
            }

            $transaction['status'] = 'completed';
        } catch (\Throwable $e) {
            $transaction['status'] = 'failed';
            $transaction['error'] = $e->getMessage();
        }

        return $transaction;
    }

    public function getTransactions(int $userId): array
    {
        return array_filter($this->transactions, function ($txn) use ($userId) {
            return $txn['user_id'] === $userId;
        });
    }

    public function getTransaction(string $transactionId): ?array
    {
        foreach ($this->transactions as $txn) {
            if ($txn['id'] === $transactionId) {
                return $txn;
            }
        }

        return null;
    }

    public function getAllTransactions(): array
    {
        return $this->transactions;
    }
}
