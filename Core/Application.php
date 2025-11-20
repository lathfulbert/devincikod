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
        
        // Normalize slashes for Windows compatibility
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        
        // Remove script path from URI if it exists (for subfolder installation)
        if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }
        
        // Ensure URI starts with /
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }
        if (strpos($uri, '/') !== 0) {
            $uri = '/' . $uri;
        }

        $this->router->dispatch($method, $uri);
    }
}
