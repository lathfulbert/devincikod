<?php

namespace Modules\RBAC\Models;

use App\Core\Database\Model;

class Permission extends Model
{
    protected static string $table = 'permissions';
    public function roles(): \App\Core\Database\ORM\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
