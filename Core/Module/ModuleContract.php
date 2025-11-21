<?php

namespace App\Core\Module;

/**
 * Interface ModuleContract
 * 
 * Defines the contract that all modules must implement.
 * Follows SOLID principles for extensibility and maintainability.
 */
interface ModuleContract
{
    /**
     * Get the unique name of the module.
     */
    public function getName(): string;

    /**
     * Get the module version.
     */
    public function getVersion(): string;

    /**
     * Get the module description.
     */
    public function getDescription(): string;

    /**
     * Get the module author.
     */
    public function getAuthor(): string;

    /**
     * Get module dependencies (other modules required).
     * 
     * @return array ['ModuleName' => 'version']
     */
    public function getDependencies(): array;

    /**
     * Register module services and bindings.
     * Called during module discovery phase.
     */
    public function register(): void;

    /**
     * Boot the module.
     * Called after all modules are registered.
     */
    public function boot(): void;

    /**
     * Get module routes.
     * 
     * @return array Route definitions
     */
    public function getRoutes(): array;

    /**
     * Get module migrations.
     * 
     * @return array Migration class names or paths
     */
    public function getMigrations(): array;

    /**
     * Get module permissions.
     * 
     * @return array Permission names
     */
    public function getPermissions(): array;

    /**
     * Get module services to register.
     * 
     * @return array Service class names
     */
    public function getServices(): array;

    /**
     * Get module configuration.
     * 
     * @return array Configuration array
     */
    public function getConfig(): array;

    /**
     * Get module assets (JS, CSS).
     * 
     * @return array ['js' => [], 'css' => []]
     */
    public function getAssets(): array;

    /**
     * Get module views directory.
     * 
     * @return string Path to views directory
     */
    public function getViews(): string;

    /**
     * Hook called when module is activated.
     */
    public function onActivate(): void;

    /**
     * Hook called when module is deactivated.
     */
    public function onDeactivate(): void;

    /**
     * Hook called when module is installed.
     */
    public function onInstall(): void;

    /**
     * Hook called when module is uninstalled.
     */
    public function onUninstall(): void;
}
