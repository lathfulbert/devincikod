<?php

namespace App\Core\Console;

use App\Core\Application;
use App\Core\Database\Database;

class Kernel
{
    public function __construct(protected Application $app) {}

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
        } elseif ($command === 'migrate:reset') {
            $this->reset();
        } elseif ($command === 'migrate:fresh') {
            $this->fresh();
        } elseif ($command === 'seed') {
            $truncate = ($flag === '--truncate');
            $this->seed($truncate);
        } elseif (str_starts_with($command ?? '', 'i18n:') || $command === 'i18n') {
            $this->handleI18nCommand($argv);
        } elseif (str_starts_with($command ?? '', 'module:')) {
            $this->handleModuleCommand($argv);
        } elseif (str_starts_with($command ?? '', 'cache:')) {
            $this->handleCacheCommand($argv);
        } elseif ($command === 'list' || $command === '--list' || $command === '--help') {
            $this->listCommands();
        } else {
            $this->listCommands();
        }
    }

    protected function handleModuleCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'module:activate' => \App\Core\Console\Command\ModuleActivateCommand::class,
            'module:deactivate' => \App\Core\Console\Command\ModuleDeactivateCommand::class,
            'module:delete' => \App\Core\Console\Command\ModuleDeleteCommand::class,
            'module:create' => \App\Core\Console\Command\ModuleCreateCommand::class,
        ];

        if (isset($commands[$commandName])) {
            $class = $commands[$commandName];
            if (class_exists($class)) {
                $cmd = new $class();
                $cmd->execute($this->app, $args);
            } else {
                echo "Command class $class not found.\n";
            }
        } else {
            echo "Unknown module command: $commandName\n";
        }
    }

    protected function handleCacheCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'cache:clear' => \App\Core\Console\Command\CacheClearCommand::class,
            'cache:stats' => \App\Core\Console\Command\CacheStatsCommand::class,
            'cache:forget' => \App\Core\Console\Command\CacheForgetCommand::class,
        ];

        if (isset($commands[$commandName])) {
            $class = $commands[$commandName];
            if (class_exists($class)) {
                $cmd = new $class();
                $cmd->execute($this->app, $args);
            } else {
                echo "Command class $class not found.\n";
            }
        } else {
            echo "Unknown cache command: $commandName\n";
            echo "Available commands: cache:clear, cache:stats, cache:forget <key>\n";
        }
    }

    protected function listCommands(): void
    {
        echo "Available commands:\n";
        echo "  migrate              Run pending migrations\n";
        echo "  migrate --down       Rollback the last batch of migrations\n";
        echo "  migrate:reset        Rollback all migrations\n";
        echo "  migrate:fresh        Drop all tables and re-run all migrations\n";
        echo "  seed                 Run seeders\n";
        echo "  seed --truncate      Truncate tables before seeding\n";
        echo "\n";
        echo "I18n Commands:\n";
        echo "  i18n:list [locale]   List all translation keys\n";
        echo "  i18n:missing [locale] Show missing translations\n";
        echo "  i18n:sync            Synchronize translations\n";
        echo "  i18n:export [locale] [file] Export translations\n";
        echo "  i18n:import [locale] [file] Import translations\n";
        echo "  i18n:cache:clear [locale] Clear translation cache\n";
        echo "  i18n:cache:stats     Show cache statistics\n";
        echo "\n";
        echo "Module Commands:\n";
        echo "  module:activate <name>   Activate a module\n";
        echo "  module:deactivate <name> Deactivate a module\n";
        echo "  module:delete <name>     Delete a module\n";
        echo "  module:create <name>     Create a new module skeleton\n";
        echo "\n";
        echo "Cache Commands:\n";
        echo "  cache:clear              Clear all cache (application + views)\n";
        echo "  cache:stats              Show cache statistics\n";
        echo "  cache:forget <key>       Delete a specific cache key\n";
        echo "\n";
        echo "Cache Helpers (PHP):\n";
        echo "  cache('key')             Get/Set cache values in code\n";
        echo "  cache_remember()         Get or set with callback\n";
        echo "  cache_has('key')            Check existence of a key\n";
    }

    protected function handleI18nCommand(array $argv): void
    {
        $fullCommand = $argv[1] ?? '';
        $locale = $argv[2] ?? null;
        $file = $argv[3] ?? null;

        // Load I18n commands
        $i18nCommands = new \App\Core\Console\Commands\I18nCommands();

        if ($fullCommand === 'i18n:list') {
            $i18nCommands->listKeys($locale);
        } elseif ($fullCommand === 'i18n:missing') {
            $i18nCommands->missing($locale);
        } elseif ($fullCommand === 'i18n:sync') {
            $i18nCommands->sync();
        } elseif ($fullCommand === 'i18n:export') {
            if (!$locale) {
                echo "Error: Locale required for export command.\n";
                echo "Usage: php sunu i18n:export [locale] [output_file]\n";
                return;
            }
            $i18nCommands->export($locale, $file);
        } elseif ($fullCommand === 'i18n:import') {
            if (!$locale || !$file) {
                echo "Error: Locale and file required for import command.\n";
                echo "Usage: php sunu i18n:import [locale] [file]\n";
                return;
            }
            $i18nCommands->import($locale, $file);
        } elseif ($fullCommand === 'i18n:cache:clear') {
            $i18nCommands->clearCache($locale);
        } elseif ($fullCommand === 'i18n:cache:stats') {
            $i18nCommands->cacheStats();
        } else {
            echo "Unknown I18n command: {$fullCommand}\n";
            echo "Available I18n commands:\n";
            echo "  i18n:list [locale]\n";
            echo "  i18n:missing [locale]\n";
            echo "  i18n:sync\n";
            echo "  i18n:export [locale] [file]\n";
            echo "  i18n:import [locale] [file]\n";
            echo "  i18n:cache:clear [locale]\n";
            echo "  i18n:cache:stats\n";
        }
    }

    protected function seed(bool $truncate = false): void
    {
        if ($truncate) {
            echo "Truncating tables...\n";
            $db = Database::getInstance();
            $driver = $db->getDriver();

            $db->query("SET FOREIGN_KEY_CHECKS = 0");

            $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                if ($table === 'migrations') continue;

                $db->query("DELETE FROM $table");

                // Reset Auto Increment
                if ($driver === 'sqlite') {
                    $db->query("DELETE FROM sqlite_sequence WHERE name = ?", [$table]);
                } else {
                    $db->query("ALTER TABLE $table AUTO_INCREMENT = 1");
                }

                echo "  Truncated: $table\n";
            }

            $db->query("SET FOREIGN_KEY_CHECKS = 1");
            echo "Tables truncated.\n";
        }

        echo "Running seeders...\n";

        $modules = $this->app->moduleManager->getModules();

        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $seederPath = $this->app->getBasePath() . "/Modules/{$moduleName}/Database/Seeders";

            if (is_dir($seederPath)) {
                $files = glob($seederPath . '/*.php');
                sort($files); // Ensure consistent order

                foreach ($files as $file) {
                    $className = basename($file, '.php');
                    $fullClassName = "\\Modules\\{$moduleName}\\Database\\Seeders\\{$className}";

                    if (class_exists($fullClassName)) {
                        $seeder = new $fullClassName();
                        if (method_exists($seeder, 'run')) {
                            echo "  Seeding: {$className}\n";
                            $seeder->run();
                        }
                    }
                }
            }
        }
    }

    protected function migrate(): void
    {
        echo "Running migrations (UP)...\n";

        try {
            $db = Database::getInstance();

            $this->ensureMigrationsTable($db);

            // Get executed migrations
            $executedMigrations = $db->query("SELECT migration FROM migrations")->fetchAll(\PDO::FETCH_COLUMN);

            // Use getAllModules() to include all discovered modules, not just enabled ones
            $modules = $this->app->moduleManager->getAllModules();

            foreach ($modules as $module) {
                $moduleName = $module->getName();
                $migrationPath = $this->app->getBasePath() . "/Modules/{$moduleName}/Database/Migrations";

                if (is_dir($migrationPath)) {
                    $files = glob($migrationPath . '/*.php');

                    // Sort files to ensure order (001_, 002_, etc.)
                    sort($files);

                    foreach ($files as $file) {
                        $className = basename($file, '.php');

                        if (in_array($className, $executedMigrations)) {
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
                            }
                        } catch (\Exception $e) {
                            // Check if error is "Table already exists"
                            if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), '42S01') !== false) {
                                echo "  ⚠️  Table already exists. Marking migration as executed: {$className}\n";
                                $db->query("INSERT INTO migrations (migration) VALUES (?)", [$className]);
                            } else {
                                echo "  ✗ Error migrating {$className}: " . $e->getMessage() . "\n";
                                echo "  Stack trace:\n" . $e->getTraceAsString() . "\n";
                            }
                        }
                    }
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

        $this->runDown($lastMigration);
    }

    protected function reset(): void
    {
        echo "Resetting all migrations...\n";

        $db = Database::getInstance();
        $this->ensureMigrationsTable($db);

        // Get all migrations ordered by ID desc
        $migrations = $db->query("SELECT * FROM migrations ORDER BY id DESC")->fetchAll();

        if (empty($migrations)) {
            echo "Nothing to reset.\n";
            return;
        }

        foreach ($migrations as $migration) {
            $this->runDown($migration);
        }

        echo "Reset completed.\n";
    }

    protected function fresh(): void
    {
        echo "Dropping all tables and re-running migrations...\n";
        $this->reset();
        $this->migrate();
    }

    protected function runDown(array $migrationRecord): void
    {
        $className = $migrationRecord['migration'];
        $db = Database::getInstance();

        // Find the file for this migration
        $modules = $this->app->moduleManager->getModules();
        $found = false;

        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $migrationPath = $this->app->getBasePath() . "/Modules/{$moduleName}/Database/Migrations";
            $file = $migrationPath . '/' . $className . '.php';

            if (file_exists($file)) {
                try {
                    $migration = require $file;

                    if (is_object($migration) && method_exists($migration, 'down')) {
                        echo "  Rolling back: {$className}\n";
                        $migration->down();

                        // Remove from DB
                        $db->query("DELETE FROM migrations WHERE id = ?", [$migrationRecord['id']]);

                        echo "  ✓ Rolled back: {$className}\n";
                        $found = true;
                        break;
                    }
                } catch (\Exception $e) {
                    echo "Error rolling back {$className}: " . $e->getMessage() . "\n";
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
