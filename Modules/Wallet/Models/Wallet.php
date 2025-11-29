<?php

namespace Modules\Wallet\Models;

use App\Core\Database\Model;

class Wallet extends Model
{
    protected static string $table = 'wallets';

    protected array $fillable = [
        'user_id',
        'balance',
        'currency',
        'status'
    ];

    protected array $casts = [
        'balance' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
