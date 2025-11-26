<?php

namespace App\Console\Commands;

use App\Core\Application;

/**
 * Make Event Command
 * 
 * Generates a new event class.
 * Usage: php sunu make:event EventName --module=ModuleName
 */
class MakeEventCommand
{
    public function execute(Application $app, array $args): void
    {
        // Parse arguments
        $name = null;
        $module = null;

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--module=')) {
                $module = substr($arg, 9);
            } elseif (!str_starts_with($arg, '--')) {
                $name = $arg;
            }
        }

        // Validation
        if (!$name) {
            echo "❌ Error: Event name is required\n";
            echo "Usage: php sunu make:event EventName --module=ModuleName\n";
            return;
        }

        if (!$module) {
            echo "❌ Error: Module name is required\n";
            echo "Usage: php sunu make:event EventName --module=ModuleName\n";
            return;
        }

        // Build paths
        $className = str_replace('/', '\\', $name);
        $fileName = basename(str_replace('\\', '/', $name)) . '.php';
        $namespace = "Modules\\{$module}\\Events";

        if (str_contains($className, '\\')) {
            $parts = explode('\\', $className);
            $fileName = array_pop($parts) . '.php';
            $namespace .= '\\' . implode('\\', $parts);
        }

        $directory = $app->getBasePath() . "/Modules/{$module}/Events";

        // Create directory if not exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $fileName;

        // Check if file exists
        if (file_exists($filePath)) {
            echo "❌ Error: Event already exists: {$filePath}\n";
            return;
        }

        // Get stub content
        $stub = $this->getStub();

        // Replace placeholders
        $content = str_replace(
            ['{{namespace}}', '{{class}}'],
            [$namespace, basename($fileName, '.php')],
            $stub
        );

        // Write file
        file_put_contents($filePath, $content);

        echo "\n";
        echo "✅ Event created successfully!\n";
        echo "📁 Location: {$filePath}\n";
        echo "📦 Class: {$namespace}\\" . basename($fileName, '.php') . "\n";
        echo "\n";
    }

    protected function getStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}};

use App\Core\Events\Event;

/**
 * {{class}} Event
 * 
 * Triggered when [describe the event here].
 */
class {{class}} extends Event
{
    /**
     * Create a new event instance
     * 
     * @param mixed $data Event data
     */
    public function __construct(mixed $data = null)
    {
        parent::__construct([
            'data' => $data,
            'timestamp' => time(),
        ]);
    }

    /**
     * Get event data
     */
    public function getData(): mixed
    {
        return $this->data['data'] ?? null;
    }
}

PHP;
    }
}
