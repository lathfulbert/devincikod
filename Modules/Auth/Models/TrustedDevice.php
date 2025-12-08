<?php

namespace Modules\Auth\Models;

use App\Core\Database\Model;
use Modules\Users\Models\User;

/**
 * Trusted Device Model
 * Stores devices that are trusted for MFA bypass (1 month validity)
 */
class TrustedDevice extends Model
{
    protected static string $table = 'trusted_devices';

    protected array $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'ip_address',
        'user_agent',
        'last_used_at',
        'expires_at'
    ];

    protected array $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who owns this trusted device
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Check if the device is still valid (not expired)
     */
    public function isValid(): bool
    {
        return $this->expires_at && strtotime($this->expires_at) > time();
    }

    /**
     * Check if device is expired
     */
    public function isExpired(): bool
    {
        return !$this->isValid();
    }

    /**
     * Extend the device trust for another month
     */
    public function extend(): bool
    {
        $this->expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));
        $this->last_used_at = date('Y-m-d H:i:s');
        $this->save();
        return true;
    }

    /**
     * Mark device as used (update last_used_at)
     */
    public function markAsUsed(): bool
    {
        $this->last_used_at = date('Y-m-d H:i:s');
        $this->save();
        return true;
    }

    /**
     * Revoke trust for this device
     */
    public function revoke(): bool
    {
        $this->delete();
        return true;
    }

    /**
     * Clean up expired devices for all users
     */
    public static function cleanupExpired(): int
    {
        $expired = static::query()
            ->where('expires_at', '<', date('Y-m-d H:i:s'))
            ->get();

        $count = 0;
        foreach ($expired as $device) {
            $device->delete();
            $count++;
        }

        return $count;
    }

    /**
     * Get all trusted devices for a user
     */
    public static function getUserDevices(int $userId): array
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->orderBy('last_used_at', 'DESC')
            ->get();
    }

    /**
     * Revoke all trusted devices for a user
     */
    public static function revokeAllForUser(int $userId): int
    {
        $devices = static::query()->where('user_id', $userId)->get();

        $count = 0;
        foreach ($devices as $device) {
            $device->delete();
            $count++;
        }

        return $count;
    }
}
