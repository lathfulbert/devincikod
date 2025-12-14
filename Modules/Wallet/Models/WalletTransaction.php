<?php

namespace Modules\Wallet\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class WalletTransaction extends Model
{
    use HasAuthor;
    protected static string $table = 'wallet_transactions';

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
        'created_by',
        'updated_by'
    ];

    protected array $casts = [
        'amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the wallet that owns the transaction
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'user_id');
    }
}
