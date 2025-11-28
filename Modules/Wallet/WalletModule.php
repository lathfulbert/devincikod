<?php

namespace Modules\Wallet;

use Core\Modules\ModuleContract;
use Core\Modules\ModuleManifest;
use Core\Router\Router;

class WalletModule implements ModuleContract
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
            name: 'Wallet',
            description: 'Wallet system for managing credits, transactions, and billing.',
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
