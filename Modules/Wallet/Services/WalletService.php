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

        // Convert each wallet to array and load user data
        foreach ($wallets as $wallet) {
            // Use Reflection to get all properties
            $walletArray = $this->objectToArray($wallet);

            // Get user_id
            $userId = $walletArray['user_id'] ?? null;

            if ($userId) {
                $user = \Modules\Auth\Models\User::find($userId);
                if ($user) {
                    // Convert user to array
                    $walletArray['user'] = $this->objectToArray($user);
                }
            }

            $result[] = $walletArray;
        }

        return $result;
    }

    /**
     * Convert object to array using Reflection
     */
    private function objectToArray($obj): array
    {
        if (!is_object($obj)) {
            return is_array($obj) ? $obj : [];
        }

        $array = [];
        $reflection = new \ReflectionClass($obj);

        // Check if object has 'attributes' property (Model class)
        if ($reflection->hasProperty('attributes')) {
            $attributesProperty = $reflection->getProperty('attributes');
            $attributesProperty->setAccessible(true);

            if ($attributesProperty->isInitialized($obj)) {
                $attributes = $attributesProperty->getValue($obj);
                if (is_array($attributes)) {
                    return $attributes;
                }
            }
        }

        // Fallback: get all public properties directly set (like PDO FETCH_CLASS does)
        foreach (get_object_vars($obj) as $key => $value) {
            // Skip internal Model properties
            if (in_array($key, ['table', 'fillable', 'casts', 'primaryKey', 'attributes'])) {
                continue;
            }
            $array[$key] = $value;
        }

        return $array;
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
            ->get()
            ->toArray();
    }
}
