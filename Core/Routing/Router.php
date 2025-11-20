<?php

namespace App\Core\Routing;

class Router
{
    protected array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function dispatch(string $method, string $uri)
    {
        foreach ($this->routes as $route) {
            $match = $this->matchRoute($route['path'], $uri);
            
            if ($route['method'] === $method && $match !== false) {
                // Handle Middleware
                if (isset($route['middleware']) && !empty($route['middleware'])) {
                    foreach ($route['middleware'] as $middleware) {
                        if (is_callable($middleware)) {
                            if (!$middleware()) {
                                return; // Middleware blocked request
                            }
                        }
                    }
                }
                
                // Call handler with parameters
                return $this->callHandler($route['handler'], $match);
            }
        }

        $this->handleNotFound();
    }

    /**
     * Call the route handler with the matched parameters.
     */
    protected function callHandler(callable|array $handler, array $params)
    {
        if (is_array($handler)) {
            [$controller, $method] = $handler;
            
            // Use reflection to get method parameters and pass them in order
            $reflection = new \ReflectionMethod($controller, $method);
            $methodParams = $reflection->getParameters();
            
            $args = [];
            foreach ($methodParams as $param) {
                $paramName = $param->getName();
                
                // If it's the first parameter and it's an array type (for $params = [])
                if ($param->getType() && $param->getType()->getName() === 'array' && $param->isDefaultValueAvailable()) {
                    $args[] = $params;
                    break;
                }
                // Otherwise, try to match by name
                elseif (isset($params[$paramName])) {
                    $args[] = $params[$paramName];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    $args[] = null;
                }
            }
            
            return call_user_func_array([$controller, $method], $args);
        } else {
            // For closures, pass params as single array argument
            return call_user_func($handler, $params);
        }
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

    /**
     * Add a route to the collection.
     */
    protected function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Load module routes.
     */
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

    /**
     * Handle 404 errors.
     */
    protected function handleNotFound(): void
    {
        http_response_code(404);
        
        // Try to render a 404 view if it exists
        $viewPath = dirname(dirname(dirname(__DIR__))) . '/templates/errors/404.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "404 Not Found";
        }
    }

    /**
     * Group routes with common attributes.
     */
    public function group(array $attributes, callable $callback): void
    {
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];
        
        // Store current context
        $previousPrefix = $this->currentPrefix ?? '';
        $previousMiddleware = $this->currentMiddleware ?? [];
        
        // Set new context
        $this->currentPrefix = $previousPrefix . $prefix;
        $this->currentMiddleware = array_merge($previousMiddleware, $middleware);
        
        // Execute callback
        $callback($this);
        
        // Restore previous context
        $this->currentPrefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }

    /**
     * Register a route with the router (for named routes).
     */
    public function name(string $name): self
    {
        if (!empty($this->routes)) {
            $lastRoute = &$this->routes[count($this->routes) - 1];
            $lastRoute['name'] = $name;
        }
        return $this;
    }

    /**
     * Get URL for a named route.
     */
    public function route(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if (isset($route['name']) && $route['name'] === $name) {
                $path = $route['path'];
                
                // Replace parameters in the path
                foreach ($params as $key => $value) {
                    $path = str_replace('{' . $key . '}', $value, $path);
                }
                
                return url($path);
            }
        }
        
        return '#';
    }
}
