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
        } elseif ($command === 'seed' || $command === 'db:seed') {
            $truncate = ($flag === '--truncate');
            $class = null;

            // Parse --class argument
            foreach ($argv as $arg) {
                if (str_starts_with($arg, '--class=')) {
                    $class = substr($arg, 8);
                }
            }

            $this->seed($truncate, $class);
        } elseif (str_starts_with($command ?? '', 'i18n:') || $command === 'i18n') {
            $this->handleI18nCommand($argv);
        } elseif (str_starts_with($command ?? '', 'module:')) {
            $this->handleModuleCommand($argv);
        } elseif (str_starts_with($command ?? '', 'cache:')) {
            $this->handleCacheCommand($argv);
        } elseif (str_starts_with($command ?? '', 'queue:')) {
            $this->handleQueueCommand($argv);
        } elseif (str_starts_with($command ?? '', 'cron:')) {
            $this->handleCronCommand($argv);
        } elseif (str_starts_with($command ?? '', 'make:')) {
            $this->handleMakeCommand($argv);
        } elseif (str_starts_with($command ?? '', 'events:')) {
            $this->handleEventsCommand($argv);
        } elseif (str_starts_with($command ?? '', 'maintenance:')) {
            $this->handleMaintenanceCommand($argv);
        } elseif (str_starts_with($command ?? '', 'key:')) {
            $this->handleKeyCommand($argv);
        } elseif ($command === 'list' || $command === '--list' || $command === '--help') {
            $this->listCommands();
        } else {
            $this->listCommands();
        }
    }

    protected function handleKeyCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'key:generate' => \App\Core\Console\Command\KeyGenerateCommand::class,
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
            echo "Unknown key command: $commandName\n";
            echo "Available commands:\n";
            echo "  key:generate         Generate a new application key\n";
            echo "  key:generate --show  Display the key instead of modifying .env\n";
            echo "  key:generate --force Override existing key\n";
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

    protected function handleQueueCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'queue:work' => \App\Core\Console\Command\QueueWorkCommand::class,
            'queue:failed' => \App\Core\Console\Command\QueueFailedCommand::class,
            'queue:retry' => \App\Core\Console\Command\QueueRetryCommand::class,
            'queue:flush' => \App\Core\Console\Command\QueueFlushCommand::class,
            'queue:restart' => \App\Core\Console\Command\QueueRestartCommand::class,
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
            echo "Unknown queue command: $commandName\n";
            echo "Available commands:\n";
            echo "  queue:work [queue]     Start a queue worker\n";
            echo "  queue:failed           List failed jobs\n";
            echo "  queue:retry <id|--all> Retry failed jobs\n";
            echo "  queue:flush [queue]    Clear all jobs from queue\n";
            echo "  queue:restart          Restart all workers\n";
        }
    }

    protected function handleCronCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'cron:run' => \App\Core\Console\Command\CronRunCommand::class,
            'cron:list' => \App\Core\Console\Command\CronListCommand::class,
            'cron:run-task' => \App\Core\Console\Command\CronRunTaskCommand::class,
            'cron:stats' => \App\Core\Console\Command\CronStatsCommand::class,
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
            echo "Unknown cron command: $commandName\n";
            echo "Available commands:\n";
            echo "  cron:run                 Run the scheduler\n";
            echo "  cron:list                List registered tasks\n";
            echo "  cron:run-task <class>    Run specific task manually\n";
            echo "  cron:stats               Show execution statistics\n";
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

    protected function handleMakeCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'make:event' => \App\Console\Commands\MakeEventCommand::class,
            'make:listener' => \App\Console\Commands\MakeListenerCommand::class,
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
            echo "Unknown make command: $commandName\n";
            echo "Available commands:\n";
            echo "  make:event EventName --module=ModuleName\n";
            echo "  make:listener ListenerName --module=ModuleName [--event=EventName] [--queued]\n";
        }
    }

    protected function handleEventsCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'events:list' => \App\Console\Commands\ListEventsCommand::class,
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
            echo "Unknown events command: $commandName\n";
            echo "Available commands: events:list\n";
        }
    }

    protected function handleMaintenanceCommand(array $argv): void
    {
        $commandName = $argv[1] ?? '';
        $args = array_slice($argv, 2);

        $commands = [
            'maintenance:up' => \App\Core\Console\Command\MaintenanceUpCommand::class,
            'maintenance:down' => \App\Core\Console\Command\MaintenanceDownCommand::class,
            'maintenance:status' => \App\Core\Console\Command\MaintenanceStatusCommand::class,
            'maintenance:schedule' => \App\Core\Console\Command\MaintenanceScheduleCommand::class,
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
            echo "Unknown maintenance command: $commandName\n";
            echo "Available commands:\n";
            echo "  maintenance:up                   Activate maintenance mode\n";
            echo "  maintenance:down                 Deactivate maintenance mode\n";
            echo "  maintenance:status               Show maintenance status\n";
            echo "  maintenance:schedule             Schedule maintenance period\n";
        }
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
        echo "Queue Commands:\n";
        echo "  queue:work [queue]       Start a queue worker\n";
        echo "  queue:work default --max-jobs=10   Process 10 jobs then stop\n";
        echo "  queue:failed             List all failed jobs\n";
        echo "  queue:retry <id>         Retry a specific failed job\n";
        echo "  queue:retry --all        Retry all failed jobs\n";
        echo "  queue:flush [queue]      Clear all jobs from a queue\n";
        echo "  queue:restart            Restart all workers gracefully\n";
        echo "\n";
        echo "Cron Commands:\n";
        echo "  cron:run                 Run the scheduler (add to system cron)\n";
        echo "  cron:list                List registered tasks\n";
        echo "  cron:run-task <class>    Run specific task manually\n";
        echo "  cron:stats               Show execution statistics\n";
        echo "\n";
        echo "Make Commands:\n";
        echo "  make:event EventName --module=ModuleName        Create a new event\n";
        echo "  make:listener ListenerName --module=ModuleName  Create a new listener\n";
        echo "  make:listener SendEmail --module=Auth --queued  Create queued listener\n";
        echo "\n";
        echo "Event Commands:\n";
        echo "  events:list              List all registered events and listeners\n";
        echo "\n";
        echo "Maintenance Commands:\n";
        echo "  maintenance:up           Activate maintenance mode\n";
        echo "  maintenance:up --message=\"Custom message\" --end=\"2025-12-10 06:00\"\n";
        echo "  maintenance:down         Deactivate maintenance mode\n";
        echo "  maintenance:status       Show current maintenance status\n";
        echo "  maintenance:schedule --start=\"2025-12-10 02:00\" --end=\"2025-12-10 06:00\"\n";
        echo "\n";
        echo "Key Commands:\n";
        echo "  key:generate             Generate a new application key\n";
        echo "  key:generate --show      Display the key instead of modifying .env\n";
        echo "  key:generate --force     Override existing key\n";
        echo "\n";
        echo "Cache Helpers (PHP):\n";
        echo "  cache('key')             Get/Set cache values in code\n";
        echo "  cache_remember()         Get or set with callback\n";
        echo "  cache_has('key')            Check existence of a key\n";
    }

    protected function seed(bool $truncate = false, ?string $specificClass = null): void
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

                    // If specific class requested, skip others
                    if ($specificClass && $fullClassName !== $specificClass && $className !== $specificClass) {
                        continue;
                    }

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
