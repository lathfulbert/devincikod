<?php

declare(strict_types=1);

namespace App\Core\Routing;

class Router
{
    protected array $routes = [];
    protected string $currentPrefix = '';
    protected array $currentMiddleware = [];
    protected ?string $currentRouteName = null;
    protected array $middlewareAliases = [];

    public function middleware(string $alias, string $class): void
    {
        $this->middlewareAliases[$alias] = $class;
    }

    public function get(string $path, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('GET', $path, $handler, $middleware);
        return $this;
    }

    public function post(string $path, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('POST', $path, $handler, $middleware);
        return $this;
    }

    public function put(string $path, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
        return $this;
    }

    public function delete(string $path, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
        return $this;
    }

    public function dispatch(string $method, string $uri)
    {
        foreach ($this->routes as $route) {
            $match = $this->matchRoute($route['path'], $uri);

            if ($route['method'] === $method && $match !== false) {
                // Store current route name for helper functions
                $this->currentRouteName = $route['name'] ?? null;

                // Handle Middleware
                if (isset($route['middleware']) && !empty($route['middleware'])) {
                    foreach ($route['middleware'] as $middleware) {

                        // Handle string middleware (alias with parameters support)
                        if (is_string($middleware)) {
                            $params = [];
                            if (strpos($middleware, ':') !== false) {
                                [$name, $paramStr] = explode(':', $middleware, 2);
                                $params = explode(',', $paramStr);
                            } else {
                                $name = $middleware;
                            }

                            if (isset($this->middlewareAliases[$name])) {
                                $className = $this->middlewareAliases[$name];
                                if (class_exists($className)) {
                                    $instance = new $className();
                                    if (method_exists($instance, 'handle')) {
                                        // Create simple request object/array
                                        $request = $_REQUEST;
                                        $next = function ($req) {
                                            return true;
                                        };

                                        $result = $instance->handle($request, $next, ...$params);

                                        // If middleware returns response (not true/null), stop dispatch
                                        if ($result !== true && $result !== null) {
                                            return;
                                        }
                                        continue;
                                    }
                                }
                            }
                        }

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

            // If controller is a class name string, instantiate it via Container
            if (is_string($controller) && class_exists($controller)) {
                $controller = \App\Core\Application::getInstance()->make($controller);
            }

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
    protected function addRoute(string $method, string $path, $handler, array $middleware = []): void
    {
        // Support "Controller@method" string syntax
        if (is_string($handler) && strpos($handler, '@') !== false) {
            $handler = explode('@', $handler);
        }

        $path = $this->currentPrefix . $path;
        $middleware = array_merge($this->currentMiddleware, $middleware);

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
            if (isset($route['method'])) {
                // Associative array format
                $this->addRoute(
                    $route['method'],
                    $route['path'],
                    $route['handler'],
                    $route['middleware'] ?? []
                );
            } else {
                // Indexed array format: [method, path, handler, middleware]
                $this->addRoute(
                    $route[0],
                    $route[1],
                    $route[2],
                    $route[3] ?? []
                );
            }
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

    /**
     * Get all registered routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get the current route name.
     * 
     * @return string|null
     */
    public function currentRouteName(): ?string
    {
        return $this->currentRouteName;
    }
}
