<?php

namespace App\Core\Providers;

use App\Core\Logging\LogManager;

class LoggingServiceProvider
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register the logging service.
     */
    public function register(): void
    {
        $this->app->singleton('log', function ($app) {
            // Use config helper or direct path
            $configPath = __DIR__ . '/../../config/logging.php';

            if (!file_exists($configPath)) {
                throw new \RuntimeException("Logging config file not found: {$configPath}");
            }

            $config = require $configPath;
            return new LogManager($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Boot logic here if needed
    }
}
