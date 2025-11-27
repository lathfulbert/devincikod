<?php

namespace App\Core;

use App\Core\Config\Config;
use App\Core\Module\ModuleManager;
use App\Core\Routing\Router;
use App\Core\View\View;

class Application
{
    protected static Application $instance;
    public Config $config;
    public Router $router;
    public ModuleManager $moduleManager;
    public View $view;
    public \App\Core\Queue\QueueManager $queue;

    public function __construct(protected string $basePath)
    {
        $this->basePath = $basePath;
        self::$instance = $this;

        // Load Helpers
        require_once __DIR__ . '/Support/helpers.php';
        require_once __DIR__ . '/Support/authorization_helpers.php';
        require_once __DIR__ . '/Support/security_helpers.php';
        require_once __DIR__ . '/Files/Helpers/file_helpers.php';

        // Load .env
        (new \App\Core\Support\DotEnv($basePath . '/.env'))->load();

        $this->config = new Config();
        $this->router = new Router();
        $this->view = new View($basePath . '/templates');

        // Initialize Queue Manager
        $this->queue = \App\Core\Queue\QueueManager::getInstance();

        // Initialize Module System with dependencies (SOLID: Dependency Injection)
        $modulesPath = $basePath . '/Modules';
        $loader = new \App\Core\Module\ModuleLoader($modulesPath);
        $registry = new \App\Core\Module\ModuleRegistry();
        $activator = new \App\Core\Module\ModuleActivator($registry);

        $this->moduleManager = new ModuleManager(
            $modulesPath,
            $loader,
            $registry,
            $activator
        );
        $this->moduleManager->discover();

        // Register Exception Handler
        $exceptionHandler = new \App\Core\Exceptions\ExceptionHandler($this);
        $exceptionHandler->register();
    }

    public static function getInstance(): Application
    {
        return self::$instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function boot(): void
    {
        // Start Session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Apply Security Headers
        \App\Core\Http\SecurityHeaders::apply();

        // Initialize CSRF Protection
        \App\Core\Security\CSRF::getInstance();

        // Load Config
        $this->config->load($this->basePath . '/config/app.php');

        // Initialize Database
        \App\Core\Database\Database::getInstance()->connect($this->config->get('database', []));

        // Initialize I18n (Internationalization)
        \App\Core\I18n\Middleware\SetLocaleMiddleware::run();

        // Register Authorization Middleware
        $this->router->middleware('can', \App\Core\Middleware\PermissionMiddleware::class);
        $this->router->middleware('role', \App\Core\Middleware\RoleMiddleware::class);
        $this->router->middleware('secure_upload', \App\Core\Files\Middleware\SecureUploadMiddleware::class);

        // Load Global Routes
        $routesPath = $this->basePath . '/routes/web.php';
        if (file_exists($routesPath)) {
            $router = $this->router;
            require $routesPath;
        }

        $this->moduleManager->discover();
        try {
            $this->moduleManager->syncToRegistry(); // Ensure new modules are in DB
        } catch (\PDOException $e) {
            // Ignore DB errors during boot (e.g. during migration)
        }
        $this->moduleManager->loadEnabledModules();
        $this->moduleManager->registerModules();

        // Load Module Routes
        foreach ($this->moduleManager->getModules() as $module) {
            $routes = $module->getRoutes();

            if (!empty($routes) && is_array($routes)) {
                // Check if it's a direct route array (indexed array format)
                // or a file path array (associative array like ['admin' => 'path/to/file'])
                $firstElement = reset($routes);

                if (is_array($firstElement) && isset($firstElement[0]) && isset($firstElement[1])) {
                    // Direct route array format: ['GET', '/path', handler, middleware]
                    $this->router->loadModuleRoutes($routes);
                } else {
                    // File path format: ['admin' => 'path/to/file']
                    foreach ($routes as $key => $routePath) {
                        if (is_string($routePath) && file_exists($routePath)) {
                            // Load route file
                            $router = $this->router;
                            require $routePath;
                        }
                    }
                }
            }

            // Load API Routes with /api prefix
            $apiRoutes = $module->getApiRoutes();
            if (!empty($apiRoutes)) {
                $this->router->group(['prefix' => '/api'], function ($router) use ($apiRoutes) {
                    $router->loadModuleRoutes($apiRoutes);
                });
            }
        }

        // Boot Modules
        $this->moduleManager->bootModules();
    }

    public function run(): void
    {
        // Handle CSRF Protection
        $csrfMiddleware = new \App\Core\Middleware\CSRFMiddleware();
        $csrfMiddleware->handle();

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $scriptDir = str_replace('\\', '/', dirname($scriptName));

        // Ensure scriptDir ends with / for consistent matching
        if (substr($scriptDir, -1) !== '/') {
            $scriptDir .= '/';
        }

        // Case 1: Request includes the full path (e.g. /sunuframework2/public/login)
        if (strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        } else {
            // Case 2: Request is rewritten (e.g. /sunuframework2/login -> /sunuframework2/public/index.php)
            // We need to check if the scriptDir ends with 'public/' and try removing it.
            $publicSegment = '/public/';
            if (substr($scriptDir, -strlen($publicSegment)) === $publicSegment) {
                $baseDir = substr($scriptDir, 0, -strlen($publicSegment) + 1); // Keep trailing slash
                if (strpos($uri, $baseDir) === 0) {
                    $uri = substr($uri, strlen($baseDir));
                }
            }
        }

        // Cleanup
        $uri = '/' . ltrim($uri, '/');

        $this->router->dispatch($method, $uri);
    }

    /*
    |--------------------------------------------------------------------------
    | Service Container Methods
    |--------------------------------------------------------------------------
    */

    protected array $bindings = [];
    protected array $instances = [];

    /**
     * Register a binding in the container.
     */
    public function bind(string $abstract, $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Register a shared binding (singleton) in the container.
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null;
    }

    /**
     * Resolve a binding from the container.
     */
    public function make(string $abstract)
    {
        // Check if we have a singleton instance
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] !== null) {
            return $this->instances[$abstract];
        }

        // Get the concrete implementation
        $concrete = $this->bindings[$abstract] ?? $abstract;

        // If it's a callable, execute it
        if (is_callable($concrete)) {
            $object = $concrete($this);
        } else {
            $object = new $concrete();
        }

        // Store if it's a singleton
        if (array_key_exists($abstract, $this->instances)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Check if a binding exists.
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }
}
