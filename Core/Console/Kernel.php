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
        } elseif ($command === 'seed') {
            $this->seed();
        } else {
            echo "Usage: php sunu [migrate|seed]\n";
        }
    }

    protected function seed(): void
    {
        echo "Running seeders...\n";
        // Hardcoded for now, ideally discovered
        if (class_exists(\Modules\RBAC\Database\Seeders\RBACSeeder::class)) {
            (new \Modules\RBAC\Database\Seeders\RBACSeeder())->run();
        }
    }

    protected function migrate(): void
    {
        echo "Running migrations...\n";
        
        $db = Database::getInstance();
        $driver = $db->getDriver();
        
        $idColumn = $driver === 'sqlite' 
            ? 'INTEGER PRIMARY KEY AUTOINCREMENT' 
            : 'INT AUTO_INCREMENT PRIMARY KEY';

        // Create migrations table if not exists
        $db->query("CREATE TABLE IF NOT EXISTS migrations (
            id $idColumn,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Get executed migrations
        $executedMigrations = $db->query("SELECT migration FROM migrations")->fetchAll(\PDO::FETCH_COLUMN);

        $modules = $this->app->moduleManager->getModules();
        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $migrationPath = $this->app->getBasePath() . "/Modules/{$moduleName}/Database/Migrations";
            
            if (is_dir($migrationPath)) {
                $files = glob($migrationPath . '/*.php');
                foreach ($files as $file) {
                    $className = basename($file, '.php');
                    
                    if (in_array($className, $executedMigrations)) {
                        continue;
                    }

                    require_once $file;
                    
                    // Namespace convention needs to be handled. 
                    $fullClassName = "Modules\\{$moduleName}\\Database\\Migrations\\{$className}";
                    
                    if (class_exists($fullClassName)) {
                        $migration = new $fullClassName();
                        echo "Migrating: {$className}\n";
                        $migration->up();
                        
                        // Log migration
                        $db->query("INSERT INTO migrations (migration) VALUES (?)", [$className]);
                        
                        echo "Migrated: {$className}\n";
                    }
                }
            }
        }
    }
}
