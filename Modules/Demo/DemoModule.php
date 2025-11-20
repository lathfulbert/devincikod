<?php

namespace Modules\Demo;

use App\Core\Module\ModuleContract;
use App\Core\Application;

class DemoModule implements ModuleContract
{
    public function getName(): string
    {
        return 'Demo';
    }

    public function register(): void
    {
        // Register services if any
    }

    public function boot(): void
    {
        // Perform boot actions
    }

    public function getRoutes(): array
    {
        return [
            [
                'method' => 'GET',
                'path' => '/demo',
                'handler' => function() {
                    $app = Application::getInstance();
                    echo $app->view->render('demo/index', ['title' => 'Demo Module']);
                }
            ],
            [
                'method' => 'GET',
                'path' => '/template-demo',
                'handler' => [new \Modules\Demo\Controllers\DemoController(), 'templateDemo']
            ]
        ];
    }
}
