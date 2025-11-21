<?php

namespace Modules\RBAC;

use App\Core\Module\AbstractModule;

class RBACModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [];  // Routes are managed by Admin module
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
