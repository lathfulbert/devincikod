<?php

namespace Modules\RBAC\Database\Seeders;

use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\Permission;
use App\Core\Database\Database;

class RBACSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();

        // Roles
        $roles = [
            ['name' => 'Administrateur', 'slug' => 'admin', 'level' => 100],
            ['name' => 'Manager', 'slug' => 'manager', 'level' => 80],
            ['name' => 'Éditeur', 'slug' => 'editor', 'level' => 60],
            ['name' => 'Rédacteur', 'slug' => 'writer', 'level' => 40],
            ['name' => 'Utilisateur', 'slug' => 'user', 'level' => 20],
        ];

        foreach ($roles as $roleData) {
            // Check if role exists
            $stmt = $db->query("SELECT id FROM roles WHERE slug = ?", [$roleData['slug']]);
            if ($stmt->fetch()) {
                echo "Role already exists: {$roleData['name']}\n";
                continue;
            }

            $role = new Role($roleData);
            $role->save();
            echo "Role created: {$roleData['name']}\n";
        }

        // Permissions
        $permissions = [
            // Users
            ['name' => 'View Users', 'slug' => 'view.users', 'group_name' => 'users'],
            ['name' => 'Create Users', 'slug' => 'create.users', 'group_name' => 'users'],
            // Content
            ['name' => 'View Posts', 'slug' => 'view.posts', 'group_name' => 'content'],
            ['name' => 'Create Posts', 'slug' => 'create.posts', 'group_name' => 'content'],
            // System
            ['name' => 'Access Admin', 'slug' => 'access.admin', 'group_name' => 'system'],
        ];

        foreach ($permissions as $permData) {
            // Check if permission exists
            $stmt = $db->query("SELECT id FROM permissions WHERE slug = ?", [$permData['slug']]);
            if ($stmt->fetch()) {
                echo "Permission already exists: {$permData['name']}\n";
                continue;
            }

            $perm = new Permission($permData);
            $perm->save();
            echo "Permission created: {$permData['name']}\n";
        }

        // Assign Permissions to Admin (All)
        // Find Admin Role by slug
        $stmt = $db->query("SELECT * FROM roles WHERE slug = 'admin'");
        $adminRole = $stmt->fetchObject(Role::class);

        if ($adminRole) {
            $allPerms = Permission::all();
            foreach ($allPerms as $perm) {
                // Check if already assigned
                $check = $db->query("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$adminRole->id, $perm->id]);
                if (!$check->fetch()) {
                    $db->query("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$adminRole->id, $perm->id]);
                }
            }
            echo "All permissions assigned to Admin\n";
        }
    }
}
