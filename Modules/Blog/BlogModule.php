<?php

namespace Modules\Blog;

use App\Core\Module\AbstractModule;

class BlogModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/blog', [\Modules\Blog\Controllers\BlogController::class, 'index']],
            ['GET', '/blog/{id}', [\Modules\Blog\Controllers\BlogController::class, 'show']],
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
