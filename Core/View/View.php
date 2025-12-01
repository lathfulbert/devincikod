<?php

namespace App\Core\View;

class View
{
    protected string $layout = 'default';
    protected string $templatePath;
    protected TemplateEngine $engine;
    protected array $templateVars = [];

    // Static cache for resolved view paths (performance optimization)
    protected static array $resolvedPathsCache = [];
    protected static bool $cacheEnabled = true;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;

        // Get base path from application
        $basePath = dirname(dirname(__DIR__));
        $cachePath = $basePath . '/storage/cache/views';

        $this->engine = new TemplateEngine($cachePath);
    }

    public function render(string $view, array $data = [], string $module = null, bool $useLayout = true, bool $reset = true): string
    {
        static $depth = 0;
        $depth++;

        file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', str_repeat("  ", $depth - 1) . "[$depth] Rendering: $view (reset=$reset)\n", FILE_APPEND);

        if ($depth > 20) {
            file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "INFINITE LOOP DETECTED!\n", FILE_APPEND);
            die("Infinite loop detected in view rendering");
        }

        // Inject View instance for use in compiled templates
        $data['__view'] = $this;

        // Store template vars (excluding __view for safety)
        $this->templateVars = array_diff_key($data, ['__view' => true]);

        extract($data);

        // Reset engine state for new render
        if ($reset) {
            $this->engine->reset();
        } else {
            // IMPORTANT: Reset extends for the new view, but keep sections
            $this->engine->setExtends('');
        }

        // Resolve file path
        $file = $this->resolveViewPath($view);

        if (!$file) {
            return "View {$view} not found.";
        }

        // Start buffering
        ob_start();

        // Compile and include the template
        $compiledPath = $this->engine->compile($file);
        require $compiledPath;

        $content = ob_get_clean();

        // Check if template extends another
        $extends = $this->engine->getExtends();

        if ($extends) {
            // Render the parent layout
            // We pass the same data, but we don't need to useLayout again recursively in the same way
            // The parent layout will yield sections that were defined in the child
            // IMPORTANT: Do NOT reset the engine, or we lose the sections we just captured!
            $depth--;
            return $this->render($extends, $data, null, false, false);
        }

        $depth--;
        return $content;
    }

    protected function resolveViewPath(string $view): ?string
    {
        // Check cache first for performance
        if (self::$cacheEnabled && isset(self::$resolvedPathsCache[$view])) {
            return self::$resolvedPathsCache[$view];
        }

        // Handle dot notation: layouts.app -> layouts/app
        $viewPath = str_replace('.', '/', $view);
        $basePath = dirname(dirname(__DIR__));

        file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "Resolving '$view' -> viewPath='$viewPath'\n", FILE_APPEND);

        // 1. Check in resources/views/backend for all backend views (PRIORITY)
        if (strpos($viewPath, 'backend/') === 0) {
            $path = $this->checkPath($basePath . '/resources/views/' . $viewPath);
            if ($path) {
                file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  => Resolved to RESOURCES: $path\n", FILE_APPEND);
                return self::$resolvedPathsCache[$view] = $path;
            }
        }

        // 2. Check in resources/views/errors for error pages
        if (strpos($viewPath, 'errors/') === 0) {
            $path = $this->checkPath($basePath . '/resources/views/' . $viewPath);
            if ($path) {
                file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  => Resolved to RESOURCES: $path\n", FILE_APPEND);
                return self::$resolvedPathsCache[$view] = $path;
            }
        }

        // 3. Check in module directories (PRIORITY for module views)
        $parts = explode('/', $viewPath);
        if (count($parts) >= 2) {
            $moduleName = ucfirst($parts[0]); // First segment is module name
            $moduleViewPath = implode('/', array_slice($parts, 1)); // Rest is the view path

            $moduleBasePath = $basePath . '/Modules/' . $moduleName . '/Views/' . $moduleViewPath;

            file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  Checking module: $moduleName, path: $moduleViewPath\n", FILE_APPEND);

            $path = $this->checkPath($moduleBasePath);
            if ($path) {
                file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  => Resolved to MODULE: $path\n", FILE_APPEND);
                return self::$resolvedPathsCache[$view] = $path;
            }
        }

        // Not found
        file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  => Failed to resolve: $view\n", FILE_APPEND);
        return null;
    }

    /**
     * Check if a view file exists (.tpl or .php)
     */
    protected function checkPath(string $basePath): ?string
    {
        $tplFile = $basePath . '.tpl';
        $phpFile = $basePath . '.php';

        if (file_exists($tplFile)) {
            return $tplFile;
        }
        if (file_exists($phpFile)) {
            return $phpFile;
        }

        return null;
    }

    /**
     * Clear the resolved paths cache (useful for development)
     */
    public static function clearCache(): void
    {
        self::$resolvedPathsCache = [];
    }

    /**
     * Render a view without layout (legacy support, or for partials).
     */
    public function make(string $view, array $data = []): string
    {
        // Partials should not reset the engine state
        return $this->render($view, $data, null, false, false);
    }

    /**
     * Set the layout (Legacy).
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    // Proxy methods for TemplateEngine

    public function startSection(string $name): void
    {
        $this->engine->startSection($name);
    }

    public function endSection(): void
    {
        $this->engine->endSection();
    }

    public function yieldSection(string $name, string $default = ''): string
    {
        return $this->engine->yieldSection($name, $default);
    }

    public function setExtends(string $layout): void
    {
        $this->engine->setExtends($layout);
    }

    public function getEngine(): TemplateEngine
    {
        return $this->engine;
    }

    public function getTemplateVars(): array
    {
        return $this->templateVars;
    }
}
