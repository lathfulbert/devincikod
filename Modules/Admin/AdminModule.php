<?php

namespace Modules\Admin;

use App\Core\Module\AbstractModule;

class AdminModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/admin', [\Modules\Admin\Controllers\AdminController::class, 'index']],
            ['GET', '/admin/dashboard', [\Modules\Admin\Controllers\AdminController::class, 'dashboard']],
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
