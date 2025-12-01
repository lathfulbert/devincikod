<?php

namespace App\Core;

use App\Core\Config\Config;
use App\Core\Container\Container;
use App\Core\Module\ModuleManager;
use App\Core\Routing\Router;
use App\Core\View\View;

class Application extends Container
{
    public Config $config;
    public Router $router;
    public ModuleManager $moduleManager;
    public View $view;
    public \App\Core\Queue\QueueManager $queue;

    public function __construct(protected string $basePath)
    {
        // Set the global container instance
        static::setInstance($this);

        // Bind the Application instance to the container
        $this->instance(Application::class, $this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);

        $this->basePath = $basePath;

        // Load Helpers
        require_once __DIR__ . '/Support/helpers.php';
        require_once __DIR__ . '/Support/authorization_helpers.php';
        require_once __DIR__ . '/Support/security_helpers.php';
        require_once __DIR__ . '/Files/Helpers/file_helpers.php';

        // Load .env
        (new \App\Core\Support\DotEnv($basePath . '/.env'))->load();

        // Bind Core Services
        $this->singleton(Config::class, function () {
            return new Config();
        });
        $this->config = $this->make(Config::class);

        $this->singleton(Router::class, function () {
            return new Router();
        });
        $this->router = $this->make(Router::class);

        $this->singleton(View::class, function ($app) {
            return new View($app->getBasePath() . '/templates');
        });
        $this->view = $this->make(View::class);

        // Initialize Queue Manager
        $this->queue = \App\Core\Queue\QueueManager::getInstance();

        // Initialize Module System with dependencies (SOLID: Dependency Injection)
        $modulesPath = $basePath . '/Modules';

        // We can now use the container to resolve these if we wanted to bind them first,
        // but for now we'll keep explicit instantiation for clarity during migration.
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

    /**
     * Get the globally available instance of the application.
     * Overrides Container::getInstance to return Application type hint.
     */
    public static function getInstance(): Application
    {
        /** @var Application */
        return static::$instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function boot(): void
    {
        // Start Session (only if not CLI)
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Apply Security Headers (only if not CLI)
        if (php_sapi_name() !== 'cli') {
            \App\Core\Http\SecurityHeaders::apply();
        }

        // Initialize CSRF Protection
        \App\Core\Security\CSRF::getInstance();

        // Load All Configuration Files
        $this->config->loadDirectory($this->basePath . '/config');

        // Initialize Database
        $defaultConnection = $this->config->get('database.default', 'mysql');
        $dbConfig = $this->config->get("database.connections.{$defaultConnection}", []);
        \App\Core\Database\Database::getInstance()->connect($dbConfig);

        // Initialize I18n (Internationalization)
        \App\Core\I18n\Middleware\SetLocaleMiddleware::run();

        // Register Authorization Middleware
        $this->router->registerMiddleware('can', \Modules\RBAC\Middleware\CheckPermission::class);
        $this->router->registerMiddleware('role', \Modules\RBAC\Middleware\CheckRole::class);
        $this->router->registerMiddleware('can_any', \Modules\RBAC\Middleware\CheckAnyPermission::class);
        $this->router->registerMiddleware('api_auth', \App\Core\Middleware\ApiAuthMiddleware::class);
        $this->router->registerMiddleware('secure_upload', \App\Core\Files\Middleware\SecureUploadMiddleware::class);

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
}
