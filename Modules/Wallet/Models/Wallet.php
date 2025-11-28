<?php

namespace Modules\Wallet\Models;

use Core\Database\Model;

class Wallet extends Model
{
    protected string $table = 'wallets';
    protected array $fillable = ['user_id', 'balance', 'currency', 'status'];
    protected array $casts = [
        'balance' => 'float',
        'user_id' => 'int'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    public function addCredit(float $amount): void
    {
        $this->balance += $amount;
        $this->save();
    }

    public function deductCredit(float $amount): void
    {
        if ($this->balance < $amount) {
            throw new \RuntimeException('Insufficient balance');
        }

        $this->balance -= $amount;
        $this->save();
    }

    public function hasBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function freeze(): void
    {
        $this->status = 'frozen';
        $this->save();
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
