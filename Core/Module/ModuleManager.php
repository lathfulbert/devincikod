<?php

namespace App\Core\Module;

/**
 * Class ModuleManager
 * 
 * Orchestrates module operations (SOLID: Orchestration only, delegates to specialized classes).
 * Follows Dependency Inversion Principle - depends on abstractions.
 */
class ModuleManager
{
    protected array $modules = [];
    protected array $loadedModules = [];

    public function __construct(
        protected string $modulesPath,
        protected ModuleLoader $loader,
        protected ModuleRegistry $registry,
        protected ModuleActivator $activator
    ) {}

    /**
     * Discover all available modules in the modules directory.
     */
    public function discover(): void
    {
        $this->modules = $this->loader->discoverAll();
    }

    /**
     * Load and boot enabled modules only.
     */
    public function loadEnabledModules(): void
    {
        $enabledModules = $this->registry->getEnabled();

        foreach ($enabledModules as $moduleData) {
            $moduleName = $moduleData['name'];

            if (isset($this->modules[$moduleName])) {
                $module = $this->modules[$moduleName];
                $this->loadedModules[$moduleName] = $module;
            }
        }
    }

    /**
     * Register all loaded modules.
     */
    public function registerModules(): void
    {
        foreach ($this->loadedModules as $module) {
            $module->register();
        }
    }

    /**
     * Boot all loaded modules.
     */
    public function bootModules(): void
    {
        foreach ($this->loadedModules as $module) {
            $module->boot();
        }
    }

    /**
     * Get a specific module by name.
     * 
     * @param string $name
     * @return ModuleContract|null
     */
    public function getModule(string $name): ?ModuleContract
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Get all discovered modules.
     * 
     * @return array<string, ModuleContract>
     */
    public function getAllModules(): array
    {
        return $this->modules;
    }

    /**
     * Get enabled modules.
     * 
     * @return array<string, ModuleContract>
     */
    public function getEnabledModules(): array
    {
        return $this->loadedModules;
    }

    /**
     * Get loaded modules (alias for getEnabledModules for BC).
     * 
     * @return array
     */
    public function getModules(): array
    {
        return array_values($this->loadedModules);
    }

    /**
     * Activate a module by name.
     * 
     * @param string $moduleName
     * @return bool
     */
    public function activateModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);

        if (!$module) {
            return false;
        }

        return $this->activator->activate($module);
    }

    /**
     * Deactivate a module by name.
     * 
     * @param string $moduleName
     * @return bool
     */
    public function deactivateModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);

        if (!$module) {
            return false;
        }

        return $this->activator->deactivate($module);
    }

    /**
     * Install a module by name.
     * 
     * @param string $moduleName
     * @return bool
     */
    public function installModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);

        if (!$module) {
            return false;
        }

        $manifestPath = $this->modulesPath . '/' . $moduleName . '/module.json';
        $manifest = ModuleManifest::fromFile($manifestPath);

        if (!$manifest) {
            return false;
        }

        return $this->activator->install($module, $manifest);
    }

    /**
     * Uninstall a module by name.
     * 
     * @param string $moduleName
     * @return bool
     */
    public function uninstallModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);

        if (!$module) {
            return false;
        }

        return $this->activator->uninstall($module);
    }

    /**
     * Sync all discovered modules to registry.
     * Registers new modules in the database.
     */
    public function syncToRegistry(): void
    {
        foreach ($this->modules as $module) {
            $moduleName = $module->getName();
            $manifestPath = $this->modulesPath . '/' . $moduleName . '/module.json';
            $manifest = ModuleManifest::fromFile($manifestPath);

            if ($manifest && !$this->registry->isInstalled($moduleName)) {
                $this->registry->register($module, $manifest);
            }
        }
    }

    /**
     * Get module registry instance.
     * 
     * @return ModuleRegistry
     */
    public function getRegistry(): ModuleRegistry
    {
        return $this->registry;
    }

    /**
     * Get module loader instance.
     * 
     * @return ModuleLoader
     */
    public function getLoader(): ModuleLoader
    {
        return $this->loader;
    }

    /**
     * Get module activator instance.
     * 
     * @return ModuleActivator
     */
    public function getActivator(): ModuleActivator
    {
        return $this->activator;
    }
}
