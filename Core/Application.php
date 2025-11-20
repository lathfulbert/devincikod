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

    public function __construct(protected string $basePath)
    {
        self::$instance = $this;
        
        // Load .env
        (new \App\Core\Support\DotEnv($basePath . '/.env'))->load();

        $this->config = new Config();
        $this->router = new Router();
        $this->view = new View($basePath . '/templates');
        $this->moduleManager = new ModuleManager($basePath . '/Modules');
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
        // Load Config
        $this->config->load($this->basePath . '/config/app.php');

        // Discover and Register Modules
        $this->moduleManager->discover();
        $this->moduleManager->registerModules();
        
        // Load Module Routes
        foreach ($this->moduleManager->getModules() as $module) {
            $this->router->loadModuleRoutes($module->getRoutes());
        }

        // Boot Modules
        $this->moduleManager->bootModules();
    }

    public function run(): void
    {
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
        }
        else {
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
