<?php

namespace Modules\I18n;

use App\Core\Module\AbstractModule;

class I18nModule extends AbstractModule
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
