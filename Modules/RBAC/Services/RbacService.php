<?php

namespace Modules\RBAC\Services;

use App\Core\Contracts\RbacProviderInterface;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\UserRole;
use Modules\RBAC\Models\RolePermission;
use App\Core\Database\Database;

class RbacService implements RbacProviderInterface
{
    public function assignRole($user, string $roleName)
    {
        $role = Role::where('slug', $roleName)->first();
        if (!$role) {
            return false;
        }

        // Check if already assigned
        $exists = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        if (!$exists) {
            $userRole = new UserRole();
            $userRole->user_id = $user->id;
            $userRole->role_id = $role->id;
            $userRole->save();
        }
        return true;
    }

    public function removeRole($user, string $roleName)
    {
        $role = Role::where('slug', $roleName)->first();
        if (!$role) {
            return false;
        }

        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        if ($userRole) {
            $userRole->delete();
        }
        return true;
    }

    public function syncRoles($user, array $roleNames)
    {
        // Remove all existing roles
        UserRole::where('user_id', $user->id)->delete();

        foreach ($roleNames as $roleName) {
            $this->assignRole($user, $roleName);
        }
    }

    public function hasRole($user, string $roleName): bool
    {
        if (!$user) return false;

        $role = Role::where('slug', $roleName)->first();
        if (!$role) return false;

        return UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->exists();
    }

    public function givePermissionToRole(string $roleName, string $permissionSlug)
    {
        $role = Role::where('slug', $roleName)->first();
        $permission = Permission::where('slug', $permissionSlug)->first();

        if (!$role || !$permission) {
            return false;
        }

        $exists = RolePermission::where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->first();

        if (!$exists) {
            $rp = new RolePermission();
            $rp->role_id = $role->id;
            $rp->permission_id = $permission->id;
            $rp->save();
        }
        return true;
    }

    public function roleHasPermission(string $roleName, string $permissionSlug): bool
    {
        $role = Role::where('slug', $roleName)->first();
        if (!$role) return false;

        // Eager load permissions if possible, or query directly
        // For now, query directly via pivot
        $permission = Permission::where('slug', $permissionSlug)->first();
        if (!$permission) return false;

        return RolePermission::where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->exists();
    }

    public function userHasPermission($user, string $permissionSlug): bool
    {
        if (!$user) return false;

        // 1. Get user roles
        $userRoles = UserRole::where('user_id', $user->id)->get();
        if (empty($userRoles)) return false;

        $roleIds = array_map(fn($ur) => $ur->role_id, $userRoles);

        // 2. Check if any of these roles have the permission
        $permission = Permission::where('slug', $permissionSlug)->first();
        if (!$permission) return false;

        return RolePermission::whereIn('role_id', $roleIds)
            ->where('permission_id', $permission->id)
            ->exists();
    }
}
