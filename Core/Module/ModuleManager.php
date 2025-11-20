<?php

namespace App\Core\Module;

class ModuleManager
{
    protected array $modules = [];
    protected array $loadedModules = [];

    public function __construct(protected string $modulesPath)
    {
    }

    public function discover(): void
    {
        $dirs = array_filter(glob($this->modulesPath . '/*'), 'is_dir');
        
        foreach ($dirs as $dir) {
            $moduleName = basename($dir);
            $className = "Modules\\{$moduleName}\\{$moduleName}Module";
            
            if (class_exists($className)) {
                // Check if module is enabled in config
                $appConfig = \App\Core\Application::getInstance()->config->get('modules', []);
                if (isset($appConfig[$moduleName]) && $appConfig[$moduleName] === true) {
                    $module = new $className();
                    if ($module instanceof ModuleContract) {
                        $this->modules[$moduleName] = $module;
                    }
                }
            }
        }
    }

    public function registerModules(): void
    {
        foreach ($this->modules as $module) {
            $module->register();
            $this->loadedModules[$module->getName()] = $module;
        }
    }

    public function bootModules(): void
    {
        foreach ($this->loadedModules as $module) {
            $module->boot();
        }
    }

    public function getModules(): array
    {
        return $this->loadedModules;
    }
}
