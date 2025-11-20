<?php

namespace Modules\Blog;

use App\Core\Module\ModuleContract;
use Modules\Blog\Controllers\BlogController;

class BlogModule implements ModuleContract
{
    public function getName(): string
    {
        return 'Blog';
    }

    public function register(): void
    {
        // Register services
    }

    public function boot(): void
    {
        // Boot logic
    }

    public function getRoutes(): array
    {
        return [
            [
                'method' => 'GET',
                'path' => '/blog',
                'handler' => [new BlogController(), 'index']
            ]
        ];
    }
}
