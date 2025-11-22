<?php

namespace Modules\Auth\Models;

use App\Core\Database\Database;
use App\Core\Database\Model;
use Modules\RBAC\Models\Role;

class User extends Model
{
    protected static string $table = 'users';

    public function roles(): \App\Core\Database\ORM\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function hasRole(string $slug): bool
    {
        foreach ($this->roles()->getResults() as $role) {
            if ($role->slug === $slug) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission(string $slug): bool
    {
        foreach ($this->roles()->getResults() as $role) {
            foreach ($role->permissions()->getResults() as $permission) {
                if ($permission->slug === $slug) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if user has a permission via Gate
     */
    public function can(string $permission, $model = null): bool
    {
        return gate()->forUser($this)->allows($permission, $model);
    }

    /**
     * Check if user does NOT have a permission via Gate
     */
    public function cannot(string $permission, $model = null): bool
    {
        return !$this->can($permission, $model);
    }
}
