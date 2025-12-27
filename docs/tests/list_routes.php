<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

$routes = $app->router->getRoutes();

echo "=== Routes enregistrées ===\n\n";

foreach ($routes as $route) {
    $path = $route['path'] ?? 'N/A';
    $method = $route['method'] ?? 'N/A';

    if (strpos($path, '/admin/api-keys') !== false || strpos($path, '/admin/settings/api') !== false) {
        echo "$method $path\n";
    }
}
