<?php

namespace Modules\Admin\Database\Seeders;

use Modules\Auth\Models\User;
use Modules\RBAC\Models\Role;
use App\Core\Database\Database;

class AdminUserSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();

        // 1. Create Super Admin Role if not exists
        $roleSlug = 'super-admin';
        $roleName = 'Super Admin';
        
        $stmt = $db->query("SELECT * FROM roles WHERE slug = ?", [$roleSlug]);
        $role = $stmt->fetchObject(Role::class);

        if (!$role) {
            $role = new Role();
            $role->name = $roleName;
            $role->slug = $roleSlug;
            // $role->level = 999; // If level column exists, otherwise ignore
            $role->save();
            echo "Role created: $roleName\n";
        } else {
            echo "Role already exists: $roleName\n";
        }

        // 2. Create Admin User
        $username = 'admin';
        $password = 'password'; // Default password
        
        $stmt = $db->query("SELECT * FROM users WHERE username = ?", [$username]);
        $user = $stmt->fetchObject(User::class);

        if (!$user) {
            $user = new User();
            $user->username = $username;
            $user->email = 'admin@example.com';
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->save();
            echo "User created: $username (password: $password)\n";
        } else {
            echo "User already exists: $username\n";
        }

        // 3. Assign Role to User
        // Check if already assigned
        $stmt = $db->query("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", [$user->id, $role->id]);
        if (!$stmt->fetch()) {
            $db->query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$user->id, $role->id]);
            echo "Assigned '$roleName' role to user '$username'\n";
        } else {
            echo "User '$username' already has '$roleName' role\n";
        }
    }
}
