<?php

namespace Modules\Wallet\Services;

class WalletService
{
    protected array $wallets = []; // In-memory storage for demo

    public function createWallet(int $userId, float $initialBalance = 0.0): array
    {
        $wallet = [
            'id' => uniqid('wallet_'),
            'user_id' => $userId,
            'balance' => $initialBalance,
            'currency' => 'USD',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->wallets[$userId] = $wallet;

        return $wallet;
    }

    public function getWallet(int $userId): ?array
    {
        return $this->wallets[$userId] ?? null;
    }

    public function getBalance(int $userId): float
    {
        $wallet = $this->getWallet($userId);
        return $wallet['balance'] ?? 0.0;
    }

    public function addCredit(int $userId, float $amount, string $description = ''): bool
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        if (!isset($this->wallets[$userId])) {
            $this->createWallet($userId);
        }

        $this->wallets[$userId]['balance'] += $amount;

        return true;
    }

    public function deductCredit(int $userId, float $amount, string $description = ''): bool
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        $wallet = $this->getWallet($userId);

        if (!$wallet) {
            throw new \RuntimeException('Wallet not found');
        }

        if ($wallet['balance'] < $amount) {
            throw new \RuntimeException('Insufficient balance');
        }

        $this->wallets[$userId]['balance'] -= $amount;

        return true;
    }

    public function hasBalance(int $userId, float $amount): bool
    {
        return $this->getBalance($userId) >= $amount;
    }

    public function freezeWallet(int $userId): void
    {
        if (isset($this->wallets[$userId])) {
            $this->wallets[$userId]['status'] = 'frozen';
        }
    }

    public function activateWallet(int $userId): void
    {
        if (isset($this->wallets[$userId])) {
            $this->wallets[$userId]['status'] = 'active';
        }
    }
}
