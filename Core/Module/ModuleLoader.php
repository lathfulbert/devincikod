<?php

namespace App\Core\Module;

/**
 * Class ModuleLoader
 * 
 * Responsible for loading and parsing modules (SRP: Single Responsibility).
 * Handles discovery, manifest parsing, and validation.
 */
class ModuleLoader
{
    public function __construct(
        protected string $modulesPath
    ) {}

    /**
     * Load a module from its path.
     * 
     * @param string $modulePath Full path to module directory
     * @return ModuleContract|null
     */
    public function load(string $modulePath): ?ModuleContract
    {
        $moduleName = basename($modulePath);
        $className = "Modules\\{$moduleName}\\{$moduleName}Module";

        if (!class_exists($className)) {
            return null;
        }

        try {
            $module = new $className();

            if (!$module instanceof ModuleContract) {
                throw new \RuntimeException("Module {$moduleName} does not implement ModuleContract");
            }

            // Validate module
            if (!$this->validateModule($module)) {
                return null;
            }

            return $module;
        } catch (\Exception $e) {
            error_log("Failed to load module {$moduleName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Load module manifest from module.json file.
     * 
     * @param string $modulePath Full path to module directory
     * @return ModuleManifest|null
     */
    public function loadManifest(string $modulePath): ?ModuleManifest
    {
        $manifestPath = $modulePath . '/module.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        try {
            $manifest = ModuleManifest::fromFile($manifestPath);
            $manifest?->validate();
            return $manifest;
        } catch (\Exception $e) {
            error_log("Failed to load manifest for {$modulePath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate a module instance.
     * 
     * @param ModuleContract $module
     * @return bool
     */
    public function validateModule(ModuleContract $module): bool
    {
        try {
            // Basic validation
            if (empty($module->getName())) {
                throw new \InvalidArgumentException("Module name cannot be empty");
            }

            if (empty($module->getVersion())) {
                throw new \InvalidArgumentException("Module version cannot be empty");
            }

            // Validate version format
            if (!preg_match('/^\d+\.\d+\.\d+/', $module->getVersion())) {
                throw new \InvalidArgumentException("Invalid version format for module {$module->getName()}");
            }

            return true;
        } catch (\Exception $e) {
            error_log("Module validation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Discover all modules in the modules directory.
     * 
     * @return array<string, ModuleContract>
     */
    public function discoverAll(): array
    {
        $modules = [];
        $dirs = array_filter(glob($this->modulesPath . '/*'), 'is_dir');

        foreach ($dirs as $dir) {
            $module = $this->load($dir);
            if ($module) {
                $modules[$module->getName()] = $module;
            }
        }

        return $modules;
    }

    /**
     * Auto-load module resources based on manifest.
     * 
     * @param ModuleContract $module
     * @param ModuleManifest $manifest
     */
    public function autoloadResources(ModuleContract $module, ModuleManifest $manifest): void
    {
        $moduleName = $module->getName();

        // Routes
        if ($manifest->shouldAutoload('routes')) {
            $this->loadRoutes($module);
        }

        // Views (register namespace)
        if ($manifest->shouldAutoload('views')) {
            $this->registerViewNamespace($module);
        }

        // Assets
        if ($manifest->shouldAutoload('assets')) {
            $this->registerAssets($module);
        }

        // Config
        if ($manifest->shouldAutoload('config')) {
            $this->loadConfig($module);
        }
    }

    /**
     * Load module routes.
     */
    protected function loadRoutes(ModuleContract $module): void
    {
        // Routes are loaded by ModuleManager via getRoutes()
        // This is just a placeholder for future enhancements
    }

    /**
     * Register view namespace for module.
     */
    protected function registerViewNamespace(ModuleContract $module): void
    {
        // View namespacing will be handled by View system
        // Format: modulename::viewname
    }

    /**
     * Register module assets.
     */
    protected function registerAssets(ModuleContract $module): void
    {
        // Assets registration for future asset pipeline
    }

    /**
     * Load module configuration.
     */
    protected function loadConfig(ModuleContract $module): void
    {
        // Config loading will merge module config with app config
    }
}
