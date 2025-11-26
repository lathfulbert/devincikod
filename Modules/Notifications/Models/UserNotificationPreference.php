<?php

namespace Modules\Notifications\Models;

use App\Core\Database\Model;

/**
 * User Notification Preference Model
 * 
 * Stores user preferences for notifications
 */
class UserNotificationPreference extends Model
{
    protected static string $table = 'user_notification_preferences';

    protected static string $primaryKey = 'user_id';

    protected array $fillable = [
        'user_id',
        'channels_enabled',
        'dnd_enabled',
        'dnd_from',
        'dnd_to',
        'timezone',
        'email_opt_in',
        'sms_opt_in',
        'push_opt_in',
    ];

    protected array $casts = [
        'channels_enabled' => 'json',
        'dnd_enabled' => 'bool',
        'email_opt_in' => 'bool',
        'sms_opt_in' => 'bool',
        'push_opt_in' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Override to handle JSON encoding
     */
    public function save(): void
    {
        // Encode JSON fields before save
        if (isset($this->attributes['channels_enabled']) && is_array($this->attributes['channels_enabled'])) {
            $this->attributes['channels_enabled'] = json_encode($this->attributes['channels_enabled']);
        }

        // Cast booleans to integers for MySQL
        $boolFields = ['dnd_enabled', 'email_opt_in', 'sms_opt_in', 'push_opt_in'];
        foreach ($boolFields as $field) {
            if (isset($this->attributes[$field])) {
                $this->attributes[$field] = (int) $this->attributes[$field];
            }
        }

        parent::save();
    }

    public function __get($key)
    {
        $value = parent::__get($key);
        if ($key === 'channels_enabled' && is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Get or create preferences for user
     */
    public static function forUser(int $userId): self
    {
        $preference = static::find($userId);

        if (!$preference) {
            $preference = static::create([
                'user_id' => $userId,
                'channels_enabled' => ['email', 'push'],
            ]);
        }

        return $preference;
    }

    /**
     * Check if channel is enabled for user
     */
    public function isChannelEnabled(string $channel): bool
    {
        $channels = $this->channels_enabled ?? ['email'];
        return in_array($channel, $channels);
    }

    /**
     * Check if user is in DND period
     */
    public function isInDND(): bool
    {
        if (!$this->dnd_enabled || !$this->dnd_from || !$this->dnd_to) {
            return false;
        }

        $now = new \DateTime('now', new \DateTimeZone($this->timezone ?? 'UTC'));
        $from = \DateTime::createFromFormat('H:i:s', $this->dnd_from, new \DateTimeZone($this->timezone ?? 'UTC'));
        $to = \DateTime::createFromFormat('H:i:s', $this->dnd_to, new \DateTimeZone($this->timezone ?? 'UTC'));

        return $now >= $from && $now <= $to;
    }

    /**
     * Check if user opted in for specific channel
     */
    public function hasOptedIn(string $channel): bool
    {
        return match ($channel) {
            'email' => $this->email_opt_in,
            'sms' => $this->sms_opt_in,
            'push' => $this->push_opt_in,
            default => true,
        };
    }
}
