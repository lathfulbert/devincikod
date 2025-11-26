<?php

namespace Modules\Notifications\Models;

use App\Core\Database\Model;

/**
 * Notification Model
 * 
 * Represents a notification entity
 */
class Notification extends Model
{
    protected static string $table = 'notifications';

    protected array $fillable = [
        'event_type',
        'data',
        'status',
        'priority',
        'scheduled_at',
    ];

    protected array $casts = [
        'data' => 'json',
        'scheduled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Override to handle JSON encoding
     */
    public function save(): void
    {
        // Encode JSON fields before save
        if (isset($this->attributes['data']) && is_array($this->attributes['data'])) {
            $this->attributes['data'] = json_encode($this->attributes['data']);
        }

        parent::save();
    }

    /**
     * Get recipients for this notification
     */
    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class, 'notification_id');
    }

    /**
     * Check if notification is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if notification is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_at !== null && $this->scheduled_at > now();
    }

    /**
     * Mark notification as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark notification as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}
