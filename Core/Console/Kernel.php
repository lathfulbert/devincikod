<?php

namespace App\Core\Console;

use App\Core\Application;
use App\Core\Database\Database;

class Kernel
{
    public function __construct(protected Application $app)
    {
    }

    public function handle(array $argv): void
    {
        $command = $argv[1] ?? null;

        if ($command === 'migrate') {
            $this->migrate();
        } else {
            echo "Usage: php sunu migrate\n";
        }
    }

    protected function migrate(): void
    {
        echo "Running migrations...\n";
        
        // Scan modules for migrations
        // For MVP, let's assume a 'migrations' folder in root or modules
        // We'll just scan Modules/{Module}/Database/Migrations
        
        $modules = $this->app->moduleManager->getModules();
        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $migrationPath = $this->app->basePath . "/Modules/{$moduleName}/Database/Migrations";
            
            if (is_dir($migrationPath)) {
                $files = glob($migrationPath . '/*.php');
                foreach ($files as $file) {
                    require_once $file;
                    $className = basename($file, '.php');
                    // Namespace convention needs to be handled. 
                    // Let's assume Modules\{Module}\Database\Migrations\{ClassName}
                    $fullClassName = "Modules\\{$moduleName}\\Database\\Migrations\\{$className}";
                    
                    if (class_exists($fullClassName)) {
                        $migration = new $fullClassName();
                        echo "Migrating: {$className}\n";
                        $migration->up();
                        echo "Migrated: {$className}\n";
                    }
                }
            }
        }
    }
}
