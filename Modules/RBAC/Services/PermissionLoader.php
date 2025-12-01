<?php

namespace Modules\RBAC\Services;

use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Services\RbacService;
use App\Core\Container\Container;

class PermissionLoader
{
    protected string $modulesPath;

    public function __construct()
    {
        $this->modulesPath = \base_path('Modules');
    }

    public function scanAndLoad()
    {
        $modules = scandir($this->modulesPath);
        $permissionsCount = 0;

        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') continue;

            $configPath = $this->modulesPath . '/' . $module . '/config/permissions.php';

            if (file_exists($configPath)) {
                $permissions = require $configPath;
                if (is_array($permissions)) {
                    $this->loadPermissions($module, $permissions);
                    $permissionsCount += count($permissions);
                }
            }
        }

        return $permissionsCount;
    }

    protected function loadPermissions(string $moduleName, array $permissions)
    {
        foreach ($permissions as $slug => $description) {
            // Check if permission exists
            $permission = Permission::where('slug', $slug)->first();

            if (!$permission) {
                $permission = new Permission();
                $permission->slug = $slug;
                $permission->name = ucwords(str_replace(['.', '-', '_'], ' ', $slug)); // Basic name generation
                $permission->description = $description;
                $permission->module = $moduleName; // Assuming we add a module column or just for tracking
                $permission->save();
            } else {
                // Update description if changed
                if ($permission->description !== $description) {
                    $permission->description = $description;
                    $permission->save();
                }
            }
        }
    }

    public function seedDefaultRoles()
    {
        $config = require \config_path('rbac.php');
        $defaultRoles = $config['default_roles'] ?? ['admin', 'user'];

        foreach ($defaultRoles as $roleSlug) {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) {
                $role = new Role();
                $role->slug = $roleSlug;
                $role->name = ucfirst($roleSlug);
                $role->save();
            }
        }
    }
}
