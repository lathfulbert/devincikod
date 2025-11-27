<?php

namespace Modules\Auth;

use App\Core\Module\AbstractModule;

class AuthModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            'web' => __DIR__ . '/Routes/web.php',
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
