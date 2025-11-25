<?php

namespace App\Core\Events;

use App\Core\Module\ModuleManager;

/**
 * Listener Provider
 * 
 * Loads and caches event listeners from module configurations.
 * Provides lazy loading of listeners.
 */
class ListenerProvider
{
    /**
     * Cached listener mappings from modules
     * 
     * @var array<string, array>
     */
    protected array $listenerMap = [];

    /**
     * Whether listeners have been loaded
     */
    protected bool $loaded = false;

    /**
     * Get listeners for a specific event
     * 
     * @param string $eventName Fully qualified event class name
     * @return array Array of listener class names
     */
    public function getListenersForEvent(string $eventName): array
    {
        // Load listeners from modules if not loaded
        if (!$this->loaded) {
            $this->loadListeners();
        }

        return $this->listenerMap[$eventName] ?? [];
    }

    /**
     * Load all listeners from active modules
     */
    protected function loadListeners(): void
    {
        $moduleManager = ModuleManager::getInstance();
        $activeModules = $moduleManager->getActiveModules();

        foreach ($activeModules as $module) {
            $this->loadModuleListeners($module);
        }

        $this->loaded = true;
    }

    /**
     * Load listeners from a specific module
     * 
     * @param string $moduleName Module name
     */
    protected function loadModuleListeners(string $moduleName): void
    {
        $eventsFile = dirname(__DIR__, 2) . "/Modules/{$moduleName}/events.php";

        if (!file_exists($eventsFile)) {
            return;
        }

        $eventConfig = require $eventsFile;

        if (!is_array($eventConfig)) {
            return;
        }

        // Merge listeners into map
        foreach ($eventConfig as $event => $listeners) {
            if (!isset($this->listenerMap[$event])) {
                $this->listenerMap[$event] = [];
            }

            // Support single listener or array of listeners
            $listeners = is_array($listeners) ? $listeners : [$listeners];

            $this->listenerMap[$event] = array_merge(
                $this->listenerMap[$event],
                $listeners
            );
        }
    }

    /**
     * Register a listener manually
     */
    public function register(string $event, string $listener): void
    {
        if (!isset($this->listenerMap[$event])) {
            $this->listenerMap[$event] = [];
        }

        $this->listenerMap[$event][] = $listener;
    }

    /**
     * Clear all cached listeners
     */
    public function clearCache(): void
    {
        $this->listenerMap = [];
        $this->loaded = false;
    }

    /**
     * Get all registered event-listener mappings
     */
    public function getAllListeners(): array
    {
        if (!$this->loaded) {
            $this->loadListeners();
        }

        return $this->listenerMap;
    }
}
