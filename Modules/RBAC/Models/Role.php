<?php

namespace Modules\RBAC\Models;

use App\Core\Database\Model;
use App\Core\Database\Database;

class Role extends Model
{
    protected static string $table = 'roles';

    public function permissions(): \App\Core\Database\ORM\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function users(): \App\Core\Database\ORM\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\Auth\Models\User::class, 'user_roles', 'role_id', 'user_id');
    }
}
