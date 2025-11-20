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
        $flag = $argv[2] ?? null;

        if ($command === 'migrate') {
            if ($flag === '--down') {
                $this->rollback();
            } else {
                $this->migrate();
            }
        } elseif ($command === 'seed') {
            $this->seed();
        } else {
            echo "Usage: php sunu [migrate|seed]\n";
            echo "       php sunu migrate --down (Rollback last migration)\n";
        }
    }

    protected function seed(): void
    {
        echo "Running seeders...\n";
        // Hardcoded for now, ideally discovered
        if (class_exists(\Modules\RBAC\Database\Seeders\RBACSeeder::class)) {
            (new \Modules\RBAC\Database\Seeders\RBACSeeder())->run();
        }
        if (class_exists(\Modules\Admin\Database\Seeders\AdminUserSeeder::class)) {
            (new \Modules\Admin\Database\Seeders\AdminUserSeeder())->run();
        }
    }

    protected function migrate(): void
    {
        echo "Running migrations (UP)...\n";
        
        $db = Database::getInstance();
        $this->ensureMigrationsTable($db);
        
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

    protected function rollback(): void
    {
        echo "Rolling back migrations (DOWN)...\n";
        
        $db = Database::getInstance();
        $this->ensureMigrationsTable($db);

        // Get last migration
        $lastMigration = $db->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 1")->fetch();

        if (!$lastMigration) {
            echo "Nothing to rollback.\n";
            return;
        }

        $className = $lastMigration['migration'];
        echo "Rolling back: {$className}\n";

        // Find the file for this migration
        $modules = $this->app->moduleManager->getModules();
        $found = false;

        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $migrationPath = $this->app->getBasePath() . "/Modules/{$moduleName}/Database/Migrations";
            $file = $migrationPath . '/' . $className . '.php';

            if (file_exists($file)) {
                require_once $file;
                $fullClassName = "Modules\\{$moduleName}\\Database\\Migrations\\{$className}";
                
                if (class_exists($fullClassName)) {
                    $migration = new $fullClassName();
                    $migration->down();
                    
                    // Remove from DB
                    $db->query("DELETE FROM migrations WHERE id = ?", [$lastMigration['id']]);
                    
                    echo "Rolled back: {$className}\n";
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            echo "Migration file not found for: {$className}\n";
        }
    }

    protected function ensureMigrationsTable(Database $db): void
    {
        $driver = $db->getDriver();
        $idColumn = $driver === 'sqlite' 
            ? 'INTEGER PRIMARY KEY AUTOINCREMENT' 
            : 'INT AUTO_INCREMENT PRIMARY KEY';

        $db->query("CREATE TABLE IF NOT EXISTS migrations (
            id $idColumn,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
}
