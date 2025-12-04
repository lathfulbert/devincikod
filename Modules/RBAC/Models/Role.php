<?php

namespace Modules\RBAC\Models;

use App\Core\Database\Model;
use App\Core\Database\Database;
use App\Core\Database\Traits\HasAuthor;
use App\Core\Database\Traits\SoftDeletes;

class Role extends Model
{
    use HasAuthor;
    use SoftDeletes;
    protected static string $table = 'roles';

    protected array $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function permissions(): \App\Core\Database\ORM\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function users(): \App\Core\Database\ORM\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\Users\Models\User::class, 'user_roles', 'role_id', 'user_id');
    }
}
