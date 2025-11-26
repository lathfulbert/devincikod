<?php

namespace Modules\Notifications\Models;

use App\Core\Database\Model;

/**
 * Notification Recipient Model
 * 
 * Represents a recipient of a notification
 */
class NotificationRecipient extends Model
{
    protected static string $table = 'notification_recipients';

    protected array $fillable = [
        'notification_id',
        'user_id',
        'channels',
        'status',
        'attempts',
    ];

    protected array $casts = [
        'channels' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Override to handle JSON encoding
     */
    public function save(): void
    {
        // Encode JSON fields before save
        if (isset($this->attributes['channels']) && is_array($this->attributes['channels'])) {
            $this->attributes['channels'] = json_encode($this->attributes['channels']);
        }

        parent::save();
    }

    public function __get($key)
    {
        $value = parent::__get($key);
        if ($key === 'channels' && is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Get the notification
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    /**
     * Get delivery logs
     */
    public function deliveryLogs()
    {
        return $this->hasMany(DeliveryLog::class, 'recipient_id');
    }

    /**
     * Increment attempt count
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update(['status' => 'sent']);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}
