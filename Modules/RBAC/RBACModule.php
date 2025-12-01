<?php

namespace Modules\RBAC;

use App\Core\Module\AbstractModule;

class RBACModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [];  // Routes are managed by Admin module
    }

    public function boot(): void
    {
        // Load permissions and seed default roles
        $loader = new \Modules\RBAC\Services\PermissionLoader();
        $loader->scanAndLoad();
        $loader->seedDefaultRoles();
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
