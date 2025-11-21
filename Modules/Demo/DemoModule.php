<?php

namespace Modules\Demo;

use App\Core\Module\AbstractModule;

class DemoModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/demo', [\Modules\Demo\Controllers\DemoController::class, 'index']],
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
