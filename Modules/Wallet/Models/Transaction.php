<?php

namespace Modules\Wallet\Models;

use Core\Database\Model;

class Transaction extends Model
{
    protected string $table = 'transactions';
    protected array $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'metadata',
        'status',
        'reference'
    ];
    protected array $casts = [
        'amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'metadata' => 'array',
        'wallet_id' => 'int',
        'user_id' => 'int'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function complete(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    public function fail(string $reason = ''): void
    {
        $this->status = 'failed';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['error'] = $reason;
            $this->metadata = $metadata;
        }
        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
