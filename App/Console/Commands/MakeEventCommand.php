<?php

namespace App\Console\Commands;

use App\Console\Command;

/**
 * Make Event Command
 * 
 * Generates a new event class.
 * Usage: php sunu make:event EventName --module=ModuleName
 */
class MakeEventCommand extends Command
{
    protected string $signature = 'make:event {name} {--module=}';
    protected string $description = 'Create a new event class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $module = $this->option('module');

        // If no module specified, ask user
        if (!$module) {
            $this->error('Please specify a module using --module=ModuleName');
            return 1;
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

        $directory = dirname(__DIR__, 3) . "/Modules/{$module}/Events";

        // Create directory if not exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $fileName;

        // Check if file exists
        if (file_exists($filePath)) {
            $this->error("Event already exists: {$filePath}");
            return 1;
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

        $this->success("Event created successfully!");
        $this->info("Location: {$filePath}");
        $this->info("Class: {$namespace}\\" . basename($fileName, '.php'));

        return 0;
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
