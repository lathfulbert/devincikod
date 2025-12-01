<?php

namespace Modules\RBAC\Models;

use App\Core\Database\Model;

class Module extends Model
{
    protected static string $table = 'modules';

    /**
     * Get all permissions for this module
     */
    public function permissions(): array
    {
        $db = \App\Core\Database\Database::getInstance();
        $sql = "SELECT * FROM permissions WHERE module_slug = ?";
        $stmt = $db->query($sql, [$this->slug]);
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Permission::class);
    }
}
