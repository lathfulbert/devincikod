<?php

namespace Modules\Settings\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class MaintenanceMode extends Model
{
    use HasAuthor;

    protected static string $table = 'maintenance_mode';

    protected array $fillable = [
        'is_enabled',
        'title',
        'message',
        'background_image',
        'background_color',
        'start_time',
        'end_time',
        'allowed_ips',
        'allowed_roles',
        'allowed_users',
        'show_countdown',
        'retry_after',
        'created_by',
        'updated_by'
    ];

    protected array $casts = [
        'is_enabled' => 'boolean',
        'show_countdown' => 'boolean',
        'retry_after' => 'integer',
        'allowed_ips' => 'json',
        'allowed_roles' => 'json',
        'allowed_users' => 'json'
    ];

    /**
     * Get the current maintenance configuration
     */
    public static function getCurrent(): ?self
    {
        return static::orderBy('id', 'DESC')->first();
    }

    /**
     * Check if maintenance mode is currently active
     */
    public static function isActive(): bool
    {
        $config = static::getCurrent();

        if (!$config || !$config->is_enabled) {
            return false;
        }

        // Check time-based activation
        $now = date('Y-m-d H:i:s');

        if ($config->start_time && $now < $config->start_time) {
            return false;
        }

        if ($config->end_time && $now > $config->end_time) {
            return false;
        }

        return true;
    }

    /**
     * Check if current user/IP is allowed to bypass maintenance
     */
    public static function isAllowed(): bool
    {
        $config = static::getCurrent();

        if (!$config) {
            return true;
        }

        // Check IP whitelist
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($config->allowed_ips && is_array($config->allowed_ips)) {
            foreach ($config->allowed_ips as $allowedIp) {
                if (trim($allowedIp) === $clientIp) {
                    return true;
                }
            }
        }

        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            return false;
        }

        $userId = $_SESSION['user']['id'] ?? null;
        $userRoleId = $_SESSION['user']['role_id'] ?? null;

        // Check user whitelist
        if ($config->allowed_users && is_array($config->allowed_users)) {
            if (in_array($userId, $config->allowed_users)) {
                return true;
            }
        }

        // Check role whitelist
        if ($config->allowed_roles && is_array($config->allowed_roles)) {
            if (in_array($userRoleId, $config->allowed_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enable maintenance mode
     */
    public static function enable(): bool
    {
        $config = static::getCurrent();

        if ($config) {
            $config->update(['is_enabled' => 1]);
            return true;
        }

        return false;
    }

    /**
     * Disable maintenance mode
     */
    public static function disable(): bool
    {
        $config = static::getCurrent();

        if ($config) {
            $config->update(['is_enabled' => 0]);
            return true;
        }

        return false;
    }

    /**
     * Get time remaining until end (in seconds)
     */
    public function getTimeRemaining(): ?int
    {
        if (!$this->end_time) {
            return null;
        }

        $now = strtotime(date('Y-m-d H:i:s'));
        $end = strtotime($this->end_time);

        $remaining = $end - $now;

        return $remaining > 0 ? $remaining : 0;
    }
}
