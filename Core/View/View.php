<?php

namespace App\Core\View;

class View
{
    protected string $layout = 'default';
    protected string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function render(string $view, array $data = [], string $module = null): string
    {
        extract($data);

        // Start buffering
        ob_start();

        // Determine view path
        // If module is provided, look in module's view directory (simplified for now)
        // For this MVP, we assume views are passed as absolute paths or relative to templatePath
        $viewFile = $this->templatePath . '/' . $view . '.php';
        
        if ($module) {
             // In a real scenario, we'd map module names to paths. 
             // For now, let's assume the view string might contain the full path or we handle it in the controller.
             // Let's stick to a simple convention: if module is set, we might look in Modules/{Module}/Views
             // But the requirements say "Modules inject their views into the main template".
        }

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "View {$view} not found.";
        }

        $content = ob_get_clean();

        // Render Layout
        ob_start();
        require $this->templatePath . '/frontend/' . $this->layout . '/layout.php';
        return ob_get_clean();
    }
}
