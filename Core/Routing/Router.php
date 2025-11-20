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
            // Check if route matches (supporting path parameters)
            $params = $this->matchRoute($route['path'], $uri);
            
            if ($route['method'] === $method && $params !== false) {
                // Handle Middleware
                if (isset($route['middleware'])) {
                    foreach ($route['middleware'] as $middleware) {
                        if (is_callable($middleware)) {
                            if (!$middleware()) {
                                return; // Middleware blocked request
                            }
                        }
                    }
                }
                
                // Call handler with parameters
                if (is_array($route['handler'])) {
                    [$controller, $method] = $route['handler'];
                    return call_user_func_array([$controller, $method], $params);
                } else {
                    return call_user_func_array($route['handler'], $params);
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    /**
     * Match a route pattern against a URI and extract parameters.
     */
    protected function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert route pattern to regex
        // Replace {param} with named capture groups
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // Extract only named parameters
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }

        return false;
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
