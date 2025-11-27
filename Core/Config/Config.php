<?php

namespace App\Core\Config;

class Config
{
    protected array $items = [];

    /**
     * Load a single configuration file
     * 
     * @param string $path Path to the configuration file
     * @param string|null $key Optional key to store the config under (defaults to filename without extension)
     */
    public function load(string $path, ?string $key = null): void
    {
        if (file_exists($path)) {
            $config = require $path;

            // If no key provided, use the filename without extension
            if ($key === null) {
                $key = basename($path, '.php');
            }

            // Store the configuration under the key
            $this->items[$key] = $config;
        }
    }

    /**
     * Load all configuration files from a directory
     * 
     * @param string $directory Path to the configuration directory
     */
    public function loadDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*.php');

        foreach ($files as $file) {
            $this->load($file);
        }
    }

    /**
     * Get a configuration value using dot notation
     * 
     * @param string $key Configuration key (e.g., 'app.name' or 'languages')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->items;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Value to set
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->items;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }

            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;
    }

    /**
     * Check if a configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->items;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            $value = $value[$segment];
        }

        return true;
    }

    /**
     * Get all configuration items
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }
}
