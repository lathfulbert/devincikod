<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Application.php';

$app = new App\Core\Application(__DIR__);
$app->boot();

// Test specific route matching
$testRoutes = [
    ['GET', '/admin/users/edit/1'],
    ['GET', '/admin/users/delete/1'],
    ['GET', '/admin/roles/edit/1'],
    ['POST', '/admin/users/update/1'],
];

echo "Testing route matching:\n\n";

foreach ($testRoutes as $test) {
    list($method, $uri) = $test;
    echo "Testing: [{$method}] {$uri}\n";

    $matched = false;
    foreach ($app->router->getRoutes() as $route) {
        if ($route['method'] === $method) {
            $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $route['path']);
            $regex = '#^' . $pattern . '$#';

            if (preg_match($regex, $uri)) {
                echo "  ✓ MATCHED: {$route['path']}\n";
                $matched = true;
                break;
            }
        }
    }

    if (!$matched) {
        echo "  ✗ NOT FOUND\n";
    }
    echo "\n";
}
