<?php

namespace App\Core\Contracts;

interface RbacProviderInterface
{
    public function assignRole($user, string $role);
    public function removeRole($user, string $role);
    public function syncRoles($user, array $roles);
    public function hasRole($user, string $role): bool;

    public function givePermissionToRole(string $role, string $permission);
    public function roleHasPermission(string $role, string $permission): bool;
    public function userHasPermission($user, string $permission): bool;
}
