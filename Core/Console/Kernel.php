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
        
        try {
            $db = Database::getInstance();
            echo "Database instance retrieved\n";
            
            $this->ensureMigrationsTable($db);
            echo "Migrations table ensured\n";
            
            // Get executed migrations
            $executedMigrations = $db->query("SELECT migration FROM migrations")->fetchAll(\PDO::FETCH_COLUMN);
            echo "Executed migrations count: " . count($executedMigrations) . "\n";

            $modules = $this->app->moduleManager->getModules();
            echo "Found " . count($modules) . " modules\n";
            
            foreach ($modules as $module) {
                $moduleName = $module->getName();
                $migrationPath = $this->app->getBasePath() . "/Modules/{$moduleName}/Database/Migrations";
                echo "Checking module: {$moduleName}\n";
                
                if (is_dir($migrationPath)) {
                    $files = glob($migrationPath . '/*.php');
                    
                    // Sort files to ensure order (001_, 002_, etc.)
                    sort($files);
                    
                    echo "  Found " . count($files) . " migration files\n";
                    
                    foreach ($files as $file) {
                        $className = basename($file, '.php');
                        
                        if (in_array($className, $executedMigrations)) {
                            echo "  Skipping (already executed): {$className}\n";
                            continue;
                        }

                        try {
                            // Include the migration file and get the returned object
                            $migration = require $file;
                            
                            // Check if it's a valid migration object
                            if (is_object($migration) && method_exists($migration, 'up')) {
                                echo "  Migrating: {$className}\n";
                                $migration->up();
                                
                                // Log migration
                                $db->query("INSERT INTO migrations (migration) VALUES (?)", [$className]);
                                
                                echo "  ✓ Migrated: {$className}\n";
                            } else {
                                echo "  Warning: {$className} did not return a valid migration object\n";
                            }
                        } catch (\Exception $e) {
                            echo "  ✗ Error migrating {$className}: " . $e->getMessage() . "\n";
                            echo "  Stack trace:\n" . $e->getTraceAsString() . "\n";
                        }
                    }
                } else {
                    echo "  Migration path does not exist: {$migrationPath}\n";
                }
            }
            
            echo "\nMigrations completed!\n";
        } catch (\Exception $e) {
            echo "Fatal error during migration: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
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
                try {
                    // Include the migration file and get the returned object
                    $migration = require $file;
                    
                    // Check if it's a valid migration object
                    if (is_object($migration) && method_exists($migration, 'down')) {
                        $migration->down();
                        
                        // Remove from DB
                        $db->query("DELETE FROM migrations WHERE id = ?", [$lastMigration['id']]);
                        
                        echo "Rolled back: {$className}\n";
                        $found = true;
                        break;
                    } else {
                        echo "Migration file exists but did not return a valid migration object.\n";
                    }
                } catch (\Exception $e) {
                    echo "Error rolling back {$className}: " . $e->getMessage() . "\n";
                    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
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
