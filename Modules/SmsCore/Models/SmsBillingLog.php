<?php

namespace Modules\SmsCore\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class SmsBillingLog extends Model
{
    use HasAuthor;

    protected static string $table = 'sms_billing_logs';

    protected array $fillable = [
        'user_id',
        'sender_id',
        'recipient',
        'country_code',
        'operator',
        'gateway',
        'sms_type',
        'segments',
        'unit_cost',
        'total_cost',
        'currency',
        'status',
        'created_by',
        'updated_by'
    ];

    protected array $casts = [
        'segments' => 'integer',
        'unit_cost' => 'float',
        'total_cost' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'user_id');
    }
}
