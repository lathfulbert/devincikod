<?php

namespace Modules\Auth\Models;

use App\Core\Database\Model;

class MfaMethod extends Model
{
    protected static string $table = 'mfa_methods';

    protected array $fillable = [
        'type',
        'provider_class',
        'is_active'
    ];

    protected array $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get all users who have enabled this MFA method
     */
    public function userSetups()
    {
        return $this->hasMany(UserMfaSetup::class, 'method_type', 'type');
    }

    /**
     * Check if this method is active
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Get provider instance
     */
    public function getProvider()
    {
        if (!class_exists($this->provider_class)) {
            throw new \Exception("Provider class {$this->provider_class} not found");
        }

        return new ($this->provider_class)();
    }
}
