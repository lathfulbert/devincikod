<?php

namespace App\Core\I18n;

/**
 * Class LanguageLoader
 * 
 * Loads translation files from multiple sources and merges them hierarchically.
 * Sources (in order of priority):
 * 1. Core language files (/languages/{locale}.json)
 * 2. Module language files (/modules/{Module}/languages/{locale}.json)
 * 3. Override files (/storage/i18n/overrides/{locale}.json)
 */
class LanguageLoader
{
    protected string $basePath;
    protected ?LanguageCache $cache;
    protected bool $cacheEnabled;

    public function __construct(string $basePath, ?LanguageCache $cache = null, bool $cacheEnabled = true)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->cache = $cache;
        $this->cacheEnabled = $cacheEnabled;
    }

    /**
     * Load all translations for a specific locale.
     */
    public function load(string $locale): array
    {
        // Check cache first
        if ($this->cacheEnabled && $this->cache && $this->cache->has($locale)) {
            return $this->cache->get($locale);
        }

        // Load from files
        $translations = $this->loadFromFiles($locale);

        // Store in cache
        if ($this->cacheEnabled && $this->cache) {
            $this->cache->set($locale, $translations);
        }

        return $translations;
    }

    /**
     * Load translations from all sources and merge them.
     */
    protected function loadFromFiles(string $locale): array
    {
        $translations = [];

        // 1. Load core translations
        $coreFile = $this->basePath . "/languages/{$locale}.json";
        if (file_exists($coreFile)) {
            $translations = $this->mergeTranslations($translations, $this->loadJsonFile($coreFile));
        }

        // 2. Load module translations
        $modulesPath = $this->basePath . '/Modules';
        if (is_dir($modulesPath)) {
            $translations = $this->loadModuleTranslations($modulesPath, $locale, $translations);
        }

        // 3. Load override translations (highest priority)
        $overrideFile = $this->basePath . "/storage/i18n/overrides/{$locale}.json";
        if (file_exists($overrideFile)) {
            $translations = $this->mergeTranslations($translations, $this->loadJsonFile($overrideFile));
        }

        return $translations;
    }

    /**
     * Load translations from all modules.
     */
    protected function loadModuleTranslations(string $modulesPath, string $locale, array $translations): array
    {
        $modules = scandir($modulesPath);

        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }

            $moduleLanguageFile = "{$modulesPath}/{$module}/languages/{$locale}.json";
            if (file_exists($moduleLanguageFile)) {
                $translations = $this->mergeTranslations($translations, $this->loadJsonFile($moduleLanguageFile));
            }
        }

        return $translations;
    }

    /**
     * Load and parse a JSON file.
     */
    protected function loadJsonFile(string $path): array
    {
        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("I18n: Failed to parse JSON file: {$path} - " . json_last_error_msg());
            return [];
        }

        return $data ?? [];
    }

    /**
     * Recursively merge translation arrays.
     * Later arrays override earlier ones.
     */
    protected function mergeTranslations(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeTranslations($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    /**
     * Get all available locales.
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        $languagesPath = $this->basePath . '/languages';

        if (!is_dir($languagesPath)) {
            return $locales;
        }

        $files = scandir($languagesPath);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $locales[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $locales;
    }

    /**
     * Invalidate cache for a specific locale.
     */
    public function invalidateCache(?string $locale = null): void
    {
        if ($this->cache) {
            if ($locale) {
                $this->cache->forget($locale);
            } else {
                $this->cache->flush();
            }
        }
    }
}
