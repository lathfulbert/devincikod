<?php

namespace Modules\Auth\Models;

use App\Core\Database\Database;
use App\Core\Database\Model;
use Modules\RBAC\Models\Role;

class User extends Model
{
    protected static string $table = 'users';

    public function roles(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT r.* FROM roles r 
            JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = ?", 
            [$this->id]
        );
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Role::class);
    }

    public function hasRole(string $slug): bool
    {
        foreach ($this->roles() as $role) {
            if ($role->slug === $slug) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission(string $slug): bool
    {
        foreach ($this->roles() as $role) {
            foreach ($role->permissions() as $permission) {
                if ($permission->slug === $slug) {
                    return true;
                }
            }
        }
        return false;
    }
}
