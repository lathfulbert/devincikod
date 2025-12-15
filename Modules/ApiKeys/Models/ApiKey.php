<?php

namespace Modules\ApiKeys\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;
use App\Core\Database\Traits\SoftDeletes;
use Modules\Users\Models\User;

class ApiKey extends Model
{
    // Propriétés publiques pour accès direct dans les vues
    public $key;
    public $name;
    public $user_id;
    public $permissions;
    public $is_active;
    public $expires_at;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    public $deleted_by;
    use HasAuthor;
    use SoftDeletes;

    protected static string $table = 'api_keys';
    protected bool $timestamps = true; // Enable timestamps

    protected array $fillable = [
        'key',
        'name',
        'user_id',
        'permissions',
        'is_active',
        'expires_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    /**
     * The user who owns this API key
     */
    public function user(): \App\Core\Database\ORM\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Generate a new API key with prefix
     *
     * @param string $prefix
     * @return string
     */
    public static function generateKey(string $prefix = 'sk_live'): string
    {
        $randomPart = bin2hex(random_bytes(24)); // 48 characters
        return $prefix . '_' . $randomPart;
    }

    /**
     * Check if the API key is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && strtotime($this->expires_at) < time()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the API key has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // No restrictions = full access
        }

        $permissions = json_decode($this->permissions, true);
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    /**
     * Check if an IP is whitelisted for this key
     *
     * @param string $ip
     * @return bool
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->ip_whitelist)) {
            return true; // No restrictions = all IPs allowed
        }

        $allowedIps = array_map('trim', explode(',', $this->ip_whitelist));
        return in_array($ip, $allowedIps);
    }

    /**
     * Update last used timestamp
     */
    public function recordUsage(): void
    {
        $this->last_used_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Get permissions as array
     *
     * @return array
     */
    public function getPermissionsArray(): array
    {
        if (empty($this->permissions)) {
            return [];
        }

        return json_decode($this->permissions, true) ?? [];
    }

    /**
     * Set permissions from array
     *
     * @param array $permissions
     */
    public function setPermissionsArray(array $permissions): void
    {
        $this->permissions = json_encode($permissions);
    }
}
