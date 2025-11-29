<?php

namespace Modules\SmsCore\Models;

use App\Core\Database\Model;

class SmsMessage extends Model
{
    protected static string $table = 'sms_messages';
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
        'gateway_response',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'error'
    ];
    protected array $casts = [
        'user_id' => 'int',
        'cost' => 'float',
        'metadata' => 'array',
        'gateway_response' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    /**
     * Override __set to call mutators
     */
    public function __set($key, $value)
    {
        $method = 'set' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';

        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Override __get to call accessors
     */
    public function __get($key)
    {
        $method = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';

        if (method_exists($this, $method)) {
            return $this->$method($this->attributes[$key] ?? null);
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Set gateway_response attribute (convert array to JSON)
     */
    public function setGatewayResponseAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['gateway_response'] = json_encode($value);
        } else {
            $this->attributes['gateway_response'] = $value;
        }
    }

    /**
     * Get gateway_response attribute (convert JSON to array)
     */
    public function getGatewayResponseAttribute($value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Set metadata attribute (convert array to JSON)
     */
    public function setMetadataAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['metadata'] = json_encode($value);
        } else {
            $this->attributes['metadata'] = $value;
        }
    }

    /**
     * Get metadata attribute (convert JSON to array)
     */
    public function getMetadataAttribute($value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    public function markAsSent(string $gatewayMessageId = ''): void
    {
        $this->status = 'sent';
        $this->sent_at = date('Y-m-d H:i:s');
        if ($gatewayMessageId) {
            $this->gateway_message_id = $gatewayMessageId;
        }
        $this->save();
    }

    public function markAsDelivered(): void
    {
        $this->status = 'delivered';
        $this->delivered_at = date('Y-m-d H:i:s');
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
        return $this->scheduled_at && strtotime($this->scheduled_at) > time();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
