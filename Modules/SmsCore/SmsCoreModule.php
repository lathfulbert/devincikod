<?php

namespace Modules\SmsCore;

use Core\Modules\ModuleContract;
use Core\Modules\ModuleManifest;
use Core\Router\Router;

class SmsCoreModule implements ModuleContract
{
    public function register(): void
    {
        // Register services here
    }

    public function boot(): void
    {
        // Boot logic here
    }

    public function getManifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'SmsCore',
            description: 'Core SMS functionality including sending, routing, and queuing.',
            version: '1.0.0',
            author: 'LathDevinci',
            dependencies: []
        );
    }

    public function registerRoutes(Router $router): void
    {
        // Register routes here
    }
}
