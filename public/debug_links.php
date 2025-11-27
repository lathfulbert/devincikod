<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

$app = new Application(dirname(__DIR__));
$app->boot();

echo "<pre>";
echo "APP_URL from config: " . var_export(config('app.url'), true) . "\n";
echo "Generated URL for 'login': " . url('login') . "\n";
echo "Generated URL for '/': " . url('/') . "\n";

echo "\n--- Registered Routes ---\n";
$routes = $app->router->getRoutes();
foreach ($routes as $route) {
    echo $route['method'] . " " . $route['path'] . "\n";
}
echo "</pre>";
