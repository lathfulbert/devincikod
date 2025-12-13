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

        // Define BASE_PATH constant for global access (used by error pages, etc.)
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', $basePath);
        }

        // Load Helpers
        require_once __DIR__ . '/Support/helpers.php';
        require_once __DIR__ . '/Support/authorization_helpers.php';
        require_once __DIR__ . '/Support/security_helpers.php';
        require_once __DIR__ . '/Files/Helpers/file_helpers.php';

        // Load .env
        (new \App\Core\Support\DotEnv($basePath . '/.env'))->load();

        // Validate APP_KEY (required for encryption)
        $this->validateAppKey();

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

        // Set Timezone from Settings
        try {
            $timezone = get_timezone();
            date_default_timezone_set($timezone);
        } catch (\Exception $e) {
            // Fallback to Africa/Abidjan if settings not available
            date_default_timezone_set('Africa/Abidjan');
        }

        // Initialize I18n (Internationalization)
        \App\Core\I18n\Middleware\SetLocaleMiddleware::run();

        // Check Maintenance Mode (before routing)
        if (php_sapi_name() !== 'cli') {
            $maintenanceMiddleware = new \App\Core\Middleware\MaintenanceMiddleware();
            $maintenanceMiddleware->handle();
        }

        // Register Authorization Middleware
        $this->router->registerMiddleware('can', \Modules\RBAC\Middleware\CheckPermission::class);
        $this->router->registerMiddleware('role', \Modules\RBAC\Middleware\CheckRole::class);
        $this->router->registerMiddleware('can_any', \Modules\RBAC\Middleware\CheckAnyPermission::class);

        // API Middleware (inspired by Laravel Sanctum) - for token-based authentication
        $this->router->registerMiddleware('api', \App\Core\Middleware\ApiMiddleware::class);

        // Legacy alias for backward compatibility
        $this->router->registerMiddleware('api_auth', \App\Core\Middleware\ApiMiddleware::class);

        $this->router->registerMiddleware('secure_upload', \App\Core\Files\Middleware\SecureUploadMiddleware::class);

        // Module Access Middleware
        $this->router->registerMiddleware('module_access', \App\Core\Module\Middleware\ModuleAccessMiddleware::class);

        // Load Global Web Routes
        $routesPath = $this->basePath . '/routes/web.php';
        if (file_exists($routesPath)) {
            $router = $this->router;
            require $routesPath;
        }

        // Load Global API Routes (with /api prefix and api middleware - like Laravel Sanctum)
        $apiRoutesPath = $this->basePath . '/routes/api.php';
        if (file_exists($apiRoutesPath)) {
            $router = $this->router;
            $router->group([
                'prefix' => '/api',
                'middleware' => ['api']
            ], function ($router) use ($apiRoutesPath) {
                require $apiRoutesPath;
            });
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

    /**
     * Validate that APP_KEY is set and not empty.
     *
     * @throws \RuntimeException
     */
    protected function validateAppKey(): void
    {
        // Skip validation for console commands
        if (php_sapi_name() === 'cli') {
            $argv = $_SERVER['argv'] ?? [];
            // Allow key:generate command to run without APP_KEY
            if (isset($argv[1]) && str_starts_with($argv[1], 'key:')) {
                return;
            }
        }

        $appKey = $_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? getenv('APP_KEY');

        if (empty($appKey)) {
            $this->displayMissingKeyError();
            exit(1);
        }

        // Validate key format (should be base64:... for AES-256-CBC)
        if (!str_starts_with($appKey, 'base64:')) {
            $this->displayInvalidKeyError();
            exit(1);
        }

        // Validate key length (should be 32 bytes for AES-256-CBC)
        $key = base64_decode(substr($appKey, 7));
        if (mb_strlen($key, '8bit') !== 32) {
            $this->displayInvalidKeyError();
            exit(1);
        }
    }

    /**
     * Display error message when APP_KEY is missing.
     */
    protected function displayMissingKeyError(): void
    {
        if (php_sapi_name() === 'cli') {
            echo "\033[31m╔══════════════════════════════════════════════════════════════╗\n";
            echo "║                                                              ║\n";
            echo "║  \033[1m⚠  ERREUR: APP_KEY non définie\033[0m\033[31m                          ║\n";
            echo "║                                                              ║\n";
            echo "╠══════════════════════════════════════════════════════════════╣\n";
            echo "║                                                              ║\n";
            echo "║  La clé d'application (APP_KEY) est requise pour la          ║\n";
            echo "║  sécurité de votre application (chiffrement, sessions).      ║\n";
            echo "║                                                              ║\n";
            echo "║  \033[33mPour générer une clé, exécutez :\033[0m\033[31m                         ║\n";
            echo "║                                                              ║\n";
            echo "║      \033[1;32mphp sunu key:generate\033[0m\033[31m                                  ║\n";
            echo "║                                                              ║\n";
            echo "╚══════════════════════════════════════════════════════════════╝\033[0m\n\n";
        } else {
            http_response_code(500);
            echo "<!DOCTYPE html>";
            echo "<html><head><meta charset='utf-8'><title>Erreur - APP_KEY manquante</title>";
            echo "<style>body{font-family:Arial,sans-serif;background:#f5f5f5;padding:50px;text-align:center}";
            echo ".error-box{background:#fff;border-left:5px solid #dc3545;padding:30px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
            echo "h1{color:#dc3545;margin-top:0}code{background:#f8f9fa;padding:10px;display:block;margin:20px 0;border-radius:5px}</style></head>";
            echo "<body><div class='error-box'>";
            echo "<h1>⚠ Erreur de Configuration</h1>";
            echo "<p><strong>La clé d'application (APP_KEY) n'est pas définie.</strong></p>";
            echo "<p>La clé APP_KEY est requise pour la sécurité de votre application (chiffrement, sessions, cookies).</p>";
            echo "<p>Pour générer une clé, exécutez la commande suivante :</p>";
            echo "<code>php sunu key:generate</code>";
            echo "</div></body></html>";
        }
    }

    /**
     * Display error message when APP_KEY is invalid.
     */
    protected function displayInvalidKeyError(): void
    {
        if (php_sapi_name() === 'cli') {
            echo "\033[31m╔══════════════════════════════════════════════════════════════╗\n";
            echo "║                                                              ║\n";
            echo "║  \033[1m⚠  ERREUR: APP_KEY invalide\033[0m\033[31m                             ║\n";
            echo "║                                                              ║\n";
            echo "╠══════════════════════════════════════════════════════════════╣\n";
            echo "║                                                              ║\n";
            echo "║  La clé d'application (APP_KEY) est invalide.                ║\n";
            echo "║  Format attendu: base64:... (32 bytes décodés)               ║\n";
            echo "║                                                              ║\n";
            echo "║  \033[33mPour générer une nouvelle clé, exécutez :\033[0m\033[31m               ║\n";
            echo "║                                                              ║\n";
            echo "║      \033[1;32mphp sunu key:generate --force\033[0m\033[31m                        ║\n";
            echo "║                                                              ║\n";
            echo "╚══════════════════════════════════════════════════════════════╝\033[0m\n\n";
        } else {
            http_response_code(500);
            echo "<!DOCTYPE html>";
            echo "<html><head><meta charset='utf-8'><title>Erreur - APP_KEY invalide</title>";
            echo "<style>body{font-family:Arial,sans-serif;background:#f5f5f5;padding:50px;text-align:center}";
            echo ".error-box{background:#fff;border-left:5px solid #dc3545;padding:30px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
            echo "h1{color:#dc3545;margin-top:0}code{background:#f8f9fa;padding:10px;display:block;margin:20px 0;border-radius:5px}</style></head>";
            echo "<body><div class='error-box'>";
            echo "<h1>⚠ Erreur de Configuration</h1>";
            echo "<p><strong>La clé d'application (APP_KEY) est invalide.</strong></p>";
            echo "<p>Format attendu: <code>base64:...</code> (32 bytes décodés pour AES-256-CBC)</p>";
            echo "<p>Pour générer une nouvelle clé, exécutez la commande suivante :</p>";
            echo "<code>php sunu key:generate --force</code>";
            echo "</div></body></html>";
        }
    }
}
