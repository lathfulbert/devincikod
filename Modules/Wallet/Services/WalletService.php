<?php

namespace Modules\Wallet\Services;

use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletTransaction;

class WalletService
{
    /**
     * Create a new wallet for a user
     */
    public function createWallet(int $userId, float $initialBalance = 0.0): Wallet
    {
        // Check if wallet already exists
        $existing = Wallet::where('user_id', $userId)->first();
        if ($existing) {
            return $existing;
        }

        return Wallet::create([
            'user_id' => $userId,
            'balance' => $initialBalance,
            'currency' => 'XOF',
            'status' => 'active'
        ]);
    }

    /**
     * Get wallet by user ID
     */
    public function getWallet(int $userId): ?Wallet
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        // Auto-create wallet if doesn't exist
        if (!$wallet) {
            $wallet = $this->createWallet($userId, 0);
        }

        return $wallet;
    }

    /**
     * Get user's balance
     */
    public function getBalance(int $userId): float
    {
        $wallet = $this->getWallet($userId);
        return $wallet ? $wallet->balance : 0.0;
    }

    /**
     * Add credit to wallet
     */
    public function addCredit(int $userId, float $amount, string $description = ''): bool
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        $wallet = $this->getWallet($userId);
        $balanceBefore = $wallet->balance;
        $balanceAfter = $balanceBefore + $amount;

        // Update wallet balance
        $wallet->update(['balance' => $balanceAfter]);

        // Record transaction
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $userId,
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description ?: 'Credit added',
            'status' => 'completed'
        ]);

        return true;
    }

    /**
     * Deduct credit from wallet
     */
    public function deductCredit(int $userId, float $amount, string $description = ''): bool
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        $wallet = $this->getWallet($userId);

        if (!$wallet) {
            throw new \RuntimeException('Wallet not found');
        }

        if ($wallet->balance < $amount) {
            throw new \RuntimeException('Insufficient balance');
        }

        $balanceBefore = $wallet->balance;
        $balanceAfter = $balanceBefore - $amount;

        // Update wallet balance
        $wallet->update(['balance' => $balanceAfter]);

        // Record transaction
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $userId,
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description ?: 'Credit deducted',
            'status' => 'completed'
        ]);

        return true;
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasBalance(int $userId, float $amount): bool
    {
        return $this->getBalance($userId) >= $amount;
    }

    /**
     * Freeze a wallet
     */
    public function freezeWallet(int $userId): void
    {
        $wallet = $this->getWallet($userId);
        if ($wallet) {
            $wallet->update(['status' => 'frozen']);
        }
    }

    /**
     * Activate a wallet
     */
    public function activateWallet(int $userId): void
    {
        $wallet = $this->getWallet($userId);
        if ($wallet) {
            $wallet->update(['status' => 'active']);
        }
    }
    /**
     * Get all wallets (for admin)
     */
    public function getAllWallets(): array
    {
        $wallets = Wallet::orderBy('created_at', 'desc')->get();
        $result = [];

        foreach ($wallets as $wallet) {
            $walletArray = $wallet->toArray();

            // Manually load user data
            $user = \Modules\Users\Models\User::find($walletArray['user_id']);
            if ($user) {
                $walletArray['user'] = $user->toArray();
            }

            $result[] = $walletArray;
        }

        return $result;
    }

    /**
     * Get wallet transactions
     */
    public function getTransactions(int $userId, int $limit = 20): array
    {
        $wallet = $this->getWallet($userId);
        if (!$wallet) {
            return [];
        }

        return WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total balance across all wallets
     */
    public function getTotalBalance(): float
    {
        $result = Wallet::selectRaw('SUM(balance) as total_balance')->first();
        return $result->total_balance ?? 0.0;
    }

    /**
     * Get total number of users with wallets
     */
    public function getTotalUsersWithWallets(): int
    {
        return Wallet::count();
    }

    /**
     * Get count of pending topup requests
     */
    public function getPendingRequestsCount(): int
    {
        return \Modules\Wallet\Models\WalletTopupRequest::where('status', 'pending')->count();
    }

    /**
     * Get recent transactions across all wallets
     */
    public function getRecentTransactions(int $limit = 10): array
    {
        return WalletTransaction::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user wallet (alias for getWallet for consistency)
     */
    public function getUserWallet(int $userId): ?Wallet
    {
        return $this->getWallet($userId);
    }
}
