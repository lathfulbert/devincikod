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
        $basePath = dirname(dirname(dirname(__DIR__)));
        $cachePath = $basePath . '/storage/cache/views';
        
        $this->engine = new TemplateEngine($cachePath);
    }

    public function render(string $view, array $data = [], string $module = null, bool $useLayout = true): string
    {
        extract($data);

        // Start buffering
        ob_start();

        // Check for .tpl file first, then fallback to .php
        $tplFile = $this->templatePath . '/' . $view . '.tpl';
        $phpFile = $this->templatePath . '/' . $view . '.php';
        
        if (file_exists($tplFile)) {
            // Compile and include the template
            $compiledPath = $this->engine->compile($tplFile);
            require $compiledPath;
        } elseif (file_exists($phpFile)) {
            require $phpFile;
        } else {
            echo "View {$view} not found (checked .tpl and .php).";
        }

        $content = ob_get_clean();

        // Render Layout if needed
        if ($useLayout) {
            ob_start();
            
            // Check for layout in .tpl or .php
            $layoutTpl = $this->templatePath . '/frontend/' . $this->layout . '/layout.tpl';
            $layoutPhp = $this->templatePath . '/frontend/' . $this->layout . '/layout.php';
            
            if (file_exists($layoutTpl)) {
                $compiledPath = $this->engine->compile($layoutTpl);
                require $compiledPath;
            } elseif (file_exists($layoutPhp)) {
                require $layoutPhp;
            }
            
            return ob_get_clean();
        }

        return $content;
    }
    
    /**
     * Render a view without layout.
     */
    public function make(string $view, array $data = []): string
    {
        return $this->render($view, $data, null, false);
    }
    
    /**
     * Set the layout.
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }
}
