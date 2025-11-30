<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

$app = new Application(dirname(__DIR__));
$app->boot();

// DEBUG: Check how many routes are registered
echo "Registered Routes: " . count($app->router->getRoutes()) . "\n";
echo "Loaded Modules: " . count($app->moduleManager->getModules()) . "\n";

// Show first 10 routes
$routes = $app->router->getRoutes();
echo "\nFirst 10 registered routes:\n";
foreach (array_slice($routes, 0, 10) as $route) {
    echo "  " . $route['method'] . " " . $route['path'] . "\n";
}
