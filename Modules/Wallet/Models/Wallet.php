<?php

namespace Modules\Wallet\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class Wallet extends Model
{
    use HasAuthor;
    protected static string $table = 'wallets';

    protected array $fillable = [
        'user_id',
        'balance',
        'currency',
        'status',
        'created_by',
        'updated_by'
    ];

    protected array $casts = [
        'balance' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
