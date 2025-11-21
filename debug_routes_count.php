<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Application.php';
$app = new App\Core\Application(__DIR__);
$app->boot();
$routes = $app->router->getRoutes();
echo "Total routes: " . count($routes) . "\n";
foreach ($routes as $r) {
    echo "[{$r['method']}] {$r['path']}\n";
}
