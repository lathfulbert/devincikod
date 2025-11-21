<?php

require_once __DIR__ . '/Core/Routing/Router.php';

// Mock for url() helper if needed, though Router uses it in route() method which we might not call
function url($path)
{
    return $path;
}

use App\Core\Routing\Router;

$router = new Router();

// Test basic route
$router->get('/basic', function () {
    return 'basic';
});

// Test grouped route
$router->group(['prefix' => '/api', 'middleware' => ['api_auth']], function ($router) {
    $router->get('/users', function () {
        return 'users';
    });

    $router->group(['prefix' => '/v1'], function ($router) {
        $router->get('/posts', function () {
            return 'posts';
        });
    });
});

$routes = $router->getRoutes();

echo "Total routes: " . count($routes) . "\n";

foreach ($routes as $route) {
    echo "Method: " . $route['method'] . "\n";
    echo "Path: " . $route['path'] . "\n";
    echo "Middleware: " . implode(', ', $route['middleware']) . "\n";
    echo "-------------------\n";
}
