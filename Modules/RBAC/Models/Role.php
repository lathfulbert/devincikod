<?php

namespace Modules\RBAC\Models;

use App\Core\Database\Model;
use App\Core\Database\Database;

class Role extends Model
{
    protected static string $table = 'roles';

    public function permissions(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT p.* FROM permissions p 
            JOIN role_permissions rp ON p.id = rp.permission_id 
            WHERE rp.role_id = ?", 
            [$this->id]
        );
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Permission::class);
    }
}
