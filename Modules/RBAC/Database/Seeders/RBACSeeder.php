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
            $perm = new Permission($permData);
            $perm->save();
            echo "Permission created: {$permData['name']}\n";
        }

        // Assign Permissions to Admin (All)
        $adminRole = Role::find(1); // Assuming ID 1 is Admin
        if ($adminRole) {
            $allPerms = Permission::all();
            foreach ($allPerms as $perm) {
                $db->query("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$adminRole->id, $perm->id]);
            }
            echo "All permissions assigned to Admin\n";
        }
    }
}
