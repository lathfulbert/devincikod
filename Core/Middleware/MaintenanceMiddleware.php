<?php

namespace App\Core\Middleware;

use Modules\Settings\Models\MaintenanceMode;

class MaintenanceMiddleware
{
    /**
     * Routes that should be excluded from maintenance mode
     */
    protected array $except = [
        '/admin/maintenance',
        '/admin/maintenance/toggle',
        '/admin/maintenance/update',
        '/api/',  // Allow API access
    ];

    public function handle(): bool
    {
        // Check if current route should be excluded
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        foreach ($this->except as $pattern) {
            if (str_starts_with($requestUri, $pattern)) {
                return true; // Allow access
            }
        }

        // Check if maintenance mode is active
        if (!MaintenanceMode::isActive()) {
            return true; // Not in maintenance, allow access
        }

        // Check if user/IP is allowed
        if (MaintenanceMode::isAllowed()) {
            return true; // Whitelisted, allow access
        }

        // Show maintenance page
        $this->showMaintenancePage();
        return false;
    }

    /**
     * Display the maintenance page
     */
    protected function showMaintenancePage(): void
    {
        $config = MaintenanceMode::getCurrent();

        // Set HTTP status code
        http_response_code(503);

        // Set Retry-After header
        if ($config && $config->retry_after) {
            header('Retry-After: ' . $config->retry_after);
        }

        // Display maintenance view
        echo view('settings/maintenance/maintenance', [
            'config' => $config,
            'title' => $config->title ?? 'Site en Maintenance'
        ]);

        exit;
    }

    public function __invoke()
    {
        return $this->handle();
    }
}
