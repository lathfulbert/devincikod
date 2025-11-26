<?php

namespace App\Console\Commands;

use App\Core\Application;

/**
 * Make Listener Command
 * 
 * Generates a new event listener class.
 * Usage: php sunu make:listener ListenerName --module=ModuleName [--event=EventName] [--queued]
 */
class MakeListenerCommand
{
    public function execute(Application $app, array $args): void
    {
        // Parse arguments
        $name = null;
        $module = null;
        $event = null;
        $queued = false;

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--module=')) {
                $module = substr($arg, 9);
            } elseif (str_starts_with($arg, '--event=')) {
                $event = substr($arg, 8);
            } elseif ($arg === '--queued') {
                $queued = true;
            } elseif (!str_starts_with($arg, '--')) {
                $name = $arg;
            }
        }

        // Validation
        if (!$name) {
            echo "❌ Error: Listener name is required\n";
            echo "Usage: php sunu make:listener ListenerName --module=ModuleName [--event=EventName] [--queued]\n";
            return;
        }

        if (!$module) {
            echo "❌ Error: Module name is required\n";
            echo "Usage: php sunu make:listener ListenerName --module=ModuleName [--event=EventName] [--queued]\n";
            return;
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

        $directory = $app->getBasePath() . "/Modules/{$module}/Listeners";

        // Create directory if not exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $fileName;

        // Check if file exists
        if (file_exists($filePath)) {
            echo "❌ Error: Listener already exists: {$filePath}\n";
            return;
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

        echo "\n";
        echo "✅ Listener created successfully!\n";
        echo "📁 Location: {$filePath}\n";
        echo "📦 Class: {$namespace}\\" . basename($fileName, '.php') . "\n";

        if ($queued) {
            echo "⚡ Type: Queued Listener (async)\n";
        }

        // Remind about events.php
        echo "\n";
        echo "⚠️  Don't forget to register this listener in:\n";
        echo "   Modules/{$module}/events.php\n";
        echo "\n";
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
