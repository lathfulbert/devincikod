<?php

namespace App\Core\Module;

use App\Core\Database\Database;

/**
 * Class ModuleActivator
 * 
 * Responsible for module lifecycle management (SRP).
 * Handles activation, deactivation, installation, and uninstallation.
 */
class ModuleActivator
{
    public function __construct(
        protected ModuleRegistry $registry
    ) {}

    /**
     * Activate a module.
     * 
     * @param ModuleContract $module
     * @return bool
     */
    public function activate(ModuleContract $module): bool
    {
        try {
            $moduleName = $module->getName();

            // Check if already enabled
            if ($this->registry->isEnabled($moduleName)) {
                return true;
            }

            // Check dependencies
            $this->checkDependencies($module);

            // Run migrations if needed
            $this->runMigrations($module);

            // Register permissions
            $this->registerPermissions($module);

            // Register services
            $this->registerServices($module);

            // Call module's onActivate hook
            $module->onActivate();

            // Mark as enabled in registry
            $this->registry->setEnabled($moduleName, true);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to activate module {$module->getName()}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deactivate a module.
     * 
     * @param ModuleContract $module
     * @return bool
     */
    public function deactivate(ModuleContract $module): bool
    {
        try {
            $moduleName = $module->getName();

            // Check if already disabled
            if (!$this->registry->isEnabled($moduleName)) {
                return true;
            }

            // Call module's onDeactivate hook
            $module->onDeactivate();

            // Mark as disabled in registry
            $this->registry->setEnabled($moduleName, false);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to deactivate module {$module->getName()}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Install a module.
     * 
     * @param ModuleContract $module
     * @param ModuleManifest $manifest
     * @return bool
     */
    public function install(ModuleContract $module, ModuleManifest $manifest): bool
    {
        try {
            $moduleName = $module->getName();

            // Check if already installed
            if ($this->registry->isInstalled($moduleName)) {
                return true;
            }

            // Register in database
            $this->registry->register($module, $manifest);

            // Run migrations
            $this->runMigrations($module);

            // Call module's onInstall hook
            $module->onInstall();

            // Mark as installed
            $this->registry->setInstalled($moduleName, true);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to install module {$module->getName()}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Uninstall a module.
     * 
     * @param ModuleContract $module
     * @return bool
     */
    public function uninstall(ModuleContract $module): bool
    {
        try {
            $moduleName = $module->getName();

            // Deactivate first if enabled
            if ($this->registry->isEnabled($moduleName)) {
                $this->deactivate($module);
            }

            // Call module's onUninstall hook
            $module->onUninstall();

            // Rollback migrations (if applicable)
            $this->rollbackMigrations($module);

            // Remove from registry
            $this->registry->unregister($moduleName);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to uninstall module {$module->getName()}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if module dependencies are satisfied.
     * 
     * @param ModuleContract $module
     * @throws \RuntimeException
     */
    protected function checkDependencies(ModuleContract $module): void
    {
        $dependencies = $module->getDependencies();

        if (empty($dependencies)) {
            return;
        }

        foreach ($dependencies as $depName => $depVersion) {
            // Skip PHP version check
            if ($depName === 'php') {
                if (version_compare(PHP_VERSION, ltrim($depVersion, '>= '), '<')) {
                    throw new \RuntimeException(
                        "Module {$module->getName()} requires PHP {$depVersion}, current version is " . PHP_VERSION
                    );
                }
                continue;
            }

            // Check if dependency module exists and is enabled
            if (!$this->registry->isEnabled($depName)) {
                throw new \RuntimeException(
                    "Module {$module->getName()} depends on module {$depName} which is not enabled"
                );
            }
        }
    }

    /**
     * Run module migrations.
     * 
     * @param ModuleContract $module
     */
    protected function runMigrations(ModuleContract $module): void
    {
        $migrations = $module->getMigrations();

        if (empty($migrations)) {
            return;
        }

        $db = Database::getInstance();

        // Ensure migrations table exists
        $this->ensureMigrationsTable($db);

        $moduleName = $module->getName();
        $migrationPath = dirname((new \ReflectionClass($module))->getFileName()) . '/Database/Migrations';

        if (!is_dir($migrationPath)) {
            return;
        }

        $files = glob($migrationPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');

            // Check if already run
            $executed = $db->query(
                "SELECT migration FROM migrations WHERE migration = ?",
                [$migrationName]
            )->fetch();

            if ($executed) {
                continue;
            }

            try {
                // Run migration
                $migration = require $file;
                if (is_object($migration) && method_exists($migration, 'up')) {
                    $migration->up();

                    // Log migration
                    $db->query(
                        "INSERT INTO migrations (migration) VALUES (?)",
                        [$migrationName]
                    );
                }
            } catch (\Exception $e) {
                error_log("Migration {$migrationName} failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Rollback module migrations.
     * 
     * @param ModuleContract $module
     */
    protected function rollbackMigrations(ModuleContract $module): void
    {
        // Rollback logic can be implemented here if needed
        // For now, we'll keep it simple
    }

    /**
     * Register module permissions.
     * 
     * @param ModuleContract $module
     */
    protected function registerPermissions(ModuleContract $module): void
    {
        $permissions = $module->getPermissions();

        if (empty($permissions)) {
            return;
        }

        $db = Database::getInstance();

        foreach ($permissions as $permission) {
            // Check if permission exists
            $exists = $db->query(
                "SELECT id FROM permissions WHERE name = ?",
                [$permission]
            )->fetch();

            if (!$exists) {
                try {
                    $db->query(
                        "INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES (?, 'web', NOW(), NOW())",
                        [$permission]
                    );
                } catch (\Exception $e) {
                    error_log("Failed to register permission {$permission}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Register module services.
     * 
     * @param ModuleContract $module
     */
    protected function registerServices(ModuleContract $module): void
    {
        $services = $module->getServices();

        if (empty($services)) {
            return;
        }

        // Service registration logic will be implemented
        // when we have a proper service container
    }

    /**
     * Ensure migrations table exists.
     */
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
