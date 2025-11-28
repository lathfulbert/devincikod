<?php

namespace Modules\SmsCore\Models;

use Core\Database\Model;

class SmsMessage extends Model
{
    protected string $table = 'sms_messages';
    protected array $fillable = [
        'user_id',
        'to',
        'from',
        'message',
        'gateway',
        'status',
        'message_id',
        'gateway_message_id',
        'cost',
        'metadata',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'error'
    ];
    protected array $casts = [
        'user_id' => 'int',
        'cost' => 'float',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    public function markAsSent(string $gatewayMessageId = ''): void
    {
        $this->status = 'sent';
        $this->sent_at = now();
        if ($gatewayMessageId) {
            $this->gateway_message_id = $gatewayMessageId;
        }
        $this->save();
    }

    public function markAsDelivered(): void
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        $this->save();
    }

    public function markAsFailed(string $error): void
    {
        $this->status = 'failed';
        $this->error = $error;
        $this->save();
    }

    public function isScheduled(): bool
    {
        return $this->scheduled_at && $this->scheduled_at > now();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
