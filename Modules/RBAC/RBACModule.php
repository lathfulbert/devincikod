<?php

namespace Modules\RBAC;

use App\Core\Module\ModuleContract;

class RBACModule implements ModuleContract
{
    public function getName(): string
    {
        return 'RBAC';
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function getRoutes(): array
    {
        return [];
    }
}
