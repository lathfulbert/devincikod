<?php

namespace App\Core\View;

class View
{
    protected string $layout = 'default';
    protected string $templatePath;
    protected TemplateEngine $engine;

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
            return $this->render($extends, $data, null, false, false);
        }

        return $content;
    }

    protected function resolveViewPath(string $view): ?string
    {
        // Handle dot notation: layouts.app -> layouts/app
        $viewPath = str_replace('.', '/', $view);

        // Check for .tpl first, then .php
        $tplFile = $this->templatePath . '/' . $viewPath . '.tpl';
        $phpFile = $this->templatePath . '/' . $viewPath . '.php';

        if (file_exists($tplFile)) return $tplFile;
        if (file_exists($phpFile)) return $phpFile;

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
}
