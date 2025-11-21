<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Application.php';

use App\Core\Application;

$app = new Application(__DIR__);

// Manually boot to trigger route loading
$app->boot();

echo "Registered Routes:\n";
$routes = $app->router->getRoutes(); // I need to expose this or use reflection if protected

if (empty($routes)) {
    echo "No routes registered.\n";
} else {
    foreach ($routes as $route) {
        echo "[{$route['method']}] {$route['path']}\n";
    }
}

// Check enabled modules
echo "\nEnabled Modules:\n";
$modules = $app->moduleManager->getEnabledModules();
if (empty($modules)) {
    echo "No enabled modules.\n";
} else {
    foreach ($modules as $name => $module) {
        echo "- $name\n";
    }
}
