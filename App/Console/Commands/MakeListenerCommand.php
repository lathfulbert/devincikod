<?php

namespace App\Console\Commands;

use App\Console\Command;

/**
 * Make Listener Command
 * 
 * Generates a new event listener class.
 * Usage: php sunu make:listener ListenerName --module=ModuleName [--event=EventName] [--queued]
 */
class MakeListenerCommand extends Command
{
    protected string $signature = 'make:listener {name} {--module=} {--event=} {--queued}';
    protected string $description = 'Create a new event listener class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $module = $this->option('module');
        $event = $this->option('event');
        $queued = $this->option('queued');

        // If no module specified, ask user
        if (!$module) {
            $this->error('Please specify a module using --module=ModuleName');
            return 1;
        }

        // Build paths
        $className = str_replace('/', '\\', $name);
        $fileName = basename(str_replace('\\', '/', $name)) . '.php';
        $namespace = "Modules\\{$module}\\Listeners";

        if (str_contains($className, '\\')) {
            $parts = explode('\\', $className);
            $fileName = array_pop($parts) . '.php';
            $namespace .= '\\' . implode('\\', $parts);
        }

        $directory = dirname(__DIR__, 3) . "/Modules/{$module}/Listeners";

        // Create directory if not exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $fileName;

        // Check if file exists
        if (file_exists($filePath)) {
            $this->error("Listener already exists: {$filePath}");
            return 1;
        }

        // Get stub content
        $stub = $queued ? $this->getQueuedStub() : $this->getStub();

        // Replace placeholders
        $eventType = $event ? "\\Modules\\{$module}\\Events\\{$event}" : 'object';

        $content = str_replace(
            ['{{namespace}}', '{{class}}', '{{eventType}}', '{{eventClass}}'],
            [$namespace, basename($fileName, '.php'), $eventType, $event ?? 'YourEvent'],
            $stub
        );

        // Write file
        file_put_contents($filePath, $content);

        $this->success("Listener created successfully!");
        $this->info("Location: {$filePath}");
        $this->info("Class: {$namespace}\\" . basename($fileName, '.php'));

        if ($queued) {
            $this->info("Type: Queued Listener (async)");
        }

        // Remind about events.php
        $this->line('');
        $this->warn("Don't forget to register this listener in:");
        $this->info("Modules/{$module}/events.php");

        return 0;
    }

    protected function getStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}};

use App\Core\Contracts\ListenerInterface;

/**
 * {{class}} Listener
 * 
 * Handles [describe what this listener does].
 */
class {{class}} implements ListenerInterface
{
    /**
     * Handle the event
     * 
     * @param object $event The event instance
     */
    public function handle(object $event): void
    {
        // TODO: Implement listener logic
        
        // Example:
        // if ($event instanceof {{eventClass}}) {
        //     $data = $event->getData();
        //     // Process the event...
        // }
    }
}

PHP;
    }

    protected function getQueuedStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}};

use App\Core\Contracts\ListenerInterface;
use App\Core\Contracts\ShouldQueue;

/**
 * {{class}} Listener
 * 
 * Handles [describe what this listener does].
 * This listener runs asynchronously via the queue.
 */
class {{class}} implements ListenerInterface, ShouldQueue
{
    /**
     * Handle the event
     * 
     * @param object $event The event instance
     */
    public function handle(object $event): void
    {
        // TODO: Implement listener logic
        
        // Example:
        // if ($event instanceof {{eventClass}}) {
        //     $data = $event->getData();
        //     // Process the event asynchronously...
        // }
    }

    /**
     * Get the queue name
     */
    public function queue(): string
    {
        return 'default';
    }

    /**
     * Get the delay before execution (in seconds)
     */
    public function delay(): int
    {
        return 0;
    }
}

PHP;
    }
}
