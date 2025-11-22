<?php

namespace App\Core\View;

class View
{
    protected string $layout = 'default';
    protected string $templatePath;
    protected TemplateEngine $engine;
    protected array $templateVars = [];

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
        // Handle dot notation: layouts.app -> layouts/app
        $viewPath = str_replace('.', '/', $view);

        // Check for .tpl first, then .php
        $tplFile = $this->templatePath . '/' . $viewPath . '.tpl';
        $phpFile = $this->templatePath . '/' . $viewPath . '.php';

        file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "Resolving '$view' -> viewPath='$viewPath'\n  tpl=$tplFile (exists=" . (file_exists($tplFile) ? 'YES' : 'NO') . ")\n  php=$phpFile (exists=" . (file_exists($phpFile) ? 'YES' : 'NO') . ")\n", FILE_APPEND);

        if (file_exists($tplFile)) {
            file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  => Resolved to: $tplFile\n", FILE_APPEND);
            return $tplFile;
        }
        if (file_exists($phpFile)) {
            file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  => Resolved to: $phpFile\n", FILE_APPEND);
            return $phpFile;
        }

        file_put_contents(__DIR__ . '/../../storage/logs/debug_view_render.log', "  => Failed to resolve: $view\n", FILE_APPEND);
        return null;
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
