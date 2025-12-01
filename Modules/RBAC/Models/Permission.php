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

    /**
     * Get the module this permission belongs to
     */
    public function module(): ?\Modules\RBAC\Models\Module
    {
        if (!isset($this->module_slug)) {
            return null;
        }

        $db = \App\Core\Database\Database::getInstance();
        $sql = "SELECT * FROM modules WHERE slug = ? LIMIT 1";
        $stmt = $db->query($sql, [$this->module_slug]);
        $result = $stmt->fetchObject(\Modules\RBAC\Models\Module::class);

        return $result ?: null;
    }
}
