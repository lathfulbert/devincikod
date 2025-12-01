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
        'is_active'
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
}
