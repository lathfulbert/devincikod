<?php

namespace Modules\Notifications\Models;

use App\Core\Database\Model;

/**
 * Delivery Log Model
 * 
 * Tracks delivery status for each notification attempt
 */
class DeliveryLog extends Model
{
    protected static string $table = 'delivery_logs';

    public $timestamps = false;

    protected array $fillable = [
        'recipient_id',
        'channel',
        'provider',
        'provider_message_id',
        'status',
        'error_message',
        'metadata',
        'attempt',
        'sent_at',
    ];

    protected array $casts = [
        'metadata' => 'json',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the recipient
     */
    public function recipient()
    {
        return $this->belongsTo(NotificationRecipient::class, 'recipient_id');
    }

    /**
     * Check if delivery was successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    /**
     * Check if delivery failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'bounced']);
    }
}
