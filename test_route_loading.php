<?php
// Test simple de routing
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Application.php';

$app = new App\Core\Application(__DIR__);
$app->boot();

echo "Routes chargées : " . count($app->router->getRoutes()) . "\n\n";

// Tester le match d'une route
$testUri = '/admin/users/edit/1';
$testMethod = 'GET';

echo "Test de matching pour [{$testMethod}] {$testUri}:\n";

$found = false;
foreach ($app->router->getRoutes() as $route) {
    if ($route['method'] === $testMethod && $route['path'] === '/admin/users/edit/{id}') {
        echo "Route trouvée: {$route['path']}\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "Route /admin/users/edit/{id} NON TROUVÉE!\n";
    echo "\nRoutes /admin/users disponibles:\n";
    foreach ($app->router->getRoutes() as $route) {
        if (strpos($route['path'], '/admin/users') === 0) {
            echo "  [{$route['method']}] {$route['path']}\n";
        }
    }
}
