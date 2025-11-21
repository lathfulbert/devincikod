<?php

namespace App\Core\Module;

/**
 * Class AbstractModule
 * 
 * Base class for modules providing default implementations.
 * Follows SOLID principles - modules can extend and override as needed.
 */
abstract class AbstractModule implements ModuleContract
{
    protected ?ModuleManifest $manifest = null;

    public function __construct()
    {
        $manifestPath = $this->getModulePath() . '/module.json';

        if (file_exists($manifestPath)) {
            $this->manifest = ModuleManifest::fromFile($manifestPath);
            $this->manifest?->validate();
        }
    }

    /**
     * Get module name from manifest or class name.
     */
    public function getName(): string
    {
        if ($this->manifest) {
            return $this->manifest->name;
        }

        // Fallback: extract from class name
        $className = (new \ReflectionClass($this))->getShortName();
        return str_replace('Module', '', $className);
    }

    /**
     * Get module version from manifest.
     */
    public function getVersion(): string
    {
        return $this->manifest?->version ?? '1.0.0';
    }

    /**
     * Get module description from manifest.
     */
    public function getDescription(): string
    {
        return $this->manifest?->description ?? '';
    }

    /**
     * Get module author from manifest.
     */
    public function getAuthor(): string
    {
        return $this->manifest?->author ?? '';
    }

    /**
     * Get module dependencies from manifest.
     */
    public function getDependencies(): array
    {
        return $this->manifest?->dependencies ?? [];
    }

    /**
     * Default register implementation (empty).
     */
    public function register(): void
    {
        // Override in child class if needed
    }

    /**
     * Default boot implementation (empty).
     */
    public function boot(): void
    {
        // Override in child class if needed
    }

    /**
     * Get routes - must be implemented by child class.
     */
    abstract public function getRoutes(): array;

    /**
     * Get migrations from manifest or empty array.
     */
    public function getMigrations(): array
    {
        return [];
    }

    /**
     * Get permissions from manifest.
     */
    public function getPermissions(): array
    {
        return $this->manifest?->permissions ?? [];
    }

    /**
     * Get services - override in child class if needed.
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * Get config from manifest settings.
     */
    public function getConfig(): array
    {
        return $this->manifest?->settings ?? [];
    }

    /**
     * Get assets from manifest.
     */
    public function getAssets(): array
    {
        return $this->manifest?->assets ?? ['js' => [], 'css' => []];
    }

    /**
     * Get views directory path.
     */
    public function getViews(): string
    {
        return $this->getModulePath() . '/Views';
    }

    /**
     * Hook: Module activation.
     */
    public function onActivate(): void
    {
        // Override in child class if needed
    }

    /**
     * Hook: Module deactivation.
     */
    public function onDeactivate(): void
    {
        // Override in child class if needed
    }

    /**
     * Hook: Module installation.
     */
    public function onInstall(): void
    {
        // Override in child class if needed
    }

    /**
     * Hook: Module uninstallation.
     */
    public function onUninstall(): void
    {
        // Override in child class if needed
    }

    /**
     * Get the module's base path.
     * Must be implemented by child class.
     */
    abstract protected function getModulePath(): string;

    /**
     * Helper: Get manifest object.
     */
    protected function getManifest(): ?ModuleManifest
    {
        return $this->manifest;
    }
}
