<?php

namespace Modules\Notifications\Models;

use App\Core\Database\Model;

/**
 * Notification Template Model
 * 
 * Stores notification templates for different channels
 */
class NotificationTemplate extends Model
{
    protected static string $table = 'notification_templates';

    protected array $fillable = [
        'name',
        'channel',
        'version',
        'subject',
        'body_html',
        'body_text',
        'body_sms',
        'push_title',
        'push_body',
        'variables',
        'active',
    ];

    protected array $casts = [
        'variables' => 'json',
        'active' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Override to handle JSON encoding
     */
    public function save(): void
    {
        // Encode JSON fields before save
        if (isset($this->attributes['variables']) && is_array($this->attributes['variables'])) {
            $this->attributes['variables'] = json_encode($this->attributes['variables']);
        }

        parent::save();
    }

    public function __get($key)
    {
        $value = parent::__get($key);
        if ($key === 'variables' && is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Get active template by name and channel
     */
    public static function findActive(string $name, string $channel): ?self
    {
        return static::where('name', $name)
            ->where('channel', $channel)
            ->where('active', true)
            ->first();
    }

    /**
     * Deactivate this template
     */
    public function deactivate(): void
    {
        $this->update(['active' => false]);
    }

    /**
     * Get variables as array
     */
    public function getVariables(): array
    {
        return $this->variables ?? [];
    }
}
