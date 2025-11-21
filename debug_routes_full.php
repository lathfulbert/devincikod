<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Application.php';
$app = new App\Core\Application(__DIR__);
$app->boot();
$routes = $app->router->getRoutes();
foreach ($routes as $route) {
    echo "[{$route['method']}] {$route['path']}\n";
}
