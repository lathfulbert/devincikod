<?php

namespace App\Core\Routing;

class Router
{
    protected array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }



    public function dispatch(string $method, string $uri)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                // Handle Middleware
                if (isset($route['middleware'])) {
                    foreach ($route['middleware'] as $middleware) {
                        // Simple middleware check (closure or class)
                        if (is_callable($middleware)) {
                            if (!$middleware()) {
                                return; // Middleware blocked request
                            }
                        }
                    }
                }
                return call_user_func($route['handler']);
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    protected function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function loadModuleRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $this->addRoute(
                $route['method'], 
                $route['path'], 
                $route['handler'], 
                $route['middleware'] ?? []
            );
        }
    }
}
