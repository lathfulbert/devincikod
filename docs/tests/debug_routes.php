<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Debug Routes ===\n";
$router = $app->router;
$routes = $router->getRoutes();
$found = false;

foreach ($routes as $route) {
    if ($route['path'] === '/admin/i18n/set-locale') {
        $found = true;
        echo "✅ FOUND: " . $route['path'] . " [" . $route['method'] . "]\n";
    }
}

if (!$found) {
    echo "❌ NOT FOUND: /admin/i18n/set-locale\n";
}

echo "\n=== Module Status ===\n";
$moduleManager = $app->moduleManager;
$modules = $moduleManager->getModules();
foreach ($modules as $name => $module) {
    echo "Module: $name\n";
}
