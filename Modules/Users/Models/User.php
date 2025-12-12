<?php

namespace Modules\Users\Models;

use App\Core\Database\Model;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\Permission;

class User extends Model
{
    protected static string $table = 'users';

    protected array $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'is_active',
        'api_key',
        'api_key_created_at'
    ];

    protected array $hidden = [
        'password',
        'remember_token'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    // Helper to check permissions directly from User model if needed,
    // though RbacService is preferred for complex logic.
    public function hasRole(string $roleSlug): bool
    {
        foreach ($this->roles as $role) {
            if ($role->slug === $roleSlug) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has a specific permission
     *
     * @param string $permission Permission slug
     * @return bool
     */
    public function can(string $permission): bool
    {
        $rbacService = \App\Core\Container\Container::getInstance()->make(\Modules\RBAC\Services\RbacService::class);
        return $rbacService->userHasPermission($this, $permission);
    }

    /**
     * Get user's MFA setups
     */
    public function mfaSetups()
    {
        return $this->hasMany(\Modules\Auth\Models\UserMfaSetup::class, 'user_id', 'id');
    }

    /**
     * Get user's OAuth accounts
     */
    public function oauthAccounts()
    {
        return $this->hasMany(\Modules\Auth\Models\OauthAccount::class, 'user_id', 'id');
    }

    /**
     * Get user's authentication logs
     */
    public function authLogs()
    {
        return $this->hasMany(\Modules\Auth\Models\AuthLog::class, 'user_id', 'id');
    }

    /**
     * Check if user has enabled MFA
     */
    public function hasMfaEnabled(): bool
    {
        $setups = \Modules\Auth\Models\UserMfaSetup::query()
            ->where('user_id', $this->id)
            ->where('is_verified', 1)
            ->get();

        return count($setups) > 0;
    }

    /**
     * Get user's verified MFA methods
     */
    public function getVerifiedMfaMethods(): array
    {
        $setups = \Modules\Auth\Models\UserMfaSetup::query()
            ->where('user_id', $this->id)
            ->where('is_verified', 1)
            ->get();

        return array_map(fn($setup) => $setup->method_type, $setups);
    }
}
