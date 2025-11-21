<?php

namespace App\Core\Console\Commands;

use App\Core\I18n\LanguageManager;

/**
 * Class I18nCommands
 * 
 * CLI commands for managing translations.
 */
class I18nCommands
{
    protected LanguageManager $manager;

    public function __construct()
    {
        $this->manager = LanguageManager::getInstance();
    }

    /**
     * List all translation keys for a locale.
     * Usage: php sunu i18n:list [locale]
     */
    public function listKeys(?string $locale = null): void
    {
        $locale = $locale ?? $this->manager->getLocale();

        if (!$this->manager->isLocaleSupported($locale)) {
            echo "Error: Locale '{$locale}' is not supported.\n";
            echo "Supported locales: " . implode(', ', $this->manager->getSupportedLocales()) . "\n";
            return;
        }

        $translations = $this->manager->all($locale);

        echo "Translation keys for locale: {$locale}\n";
        echo str_repeat('=', 50) . "\n\n";

        $this->printKeys($translations);

        echo "\nTotal keys: " . $this->countKeys($translations) . "\n";
    }

    /**
     * Show missing translations for a locale.
     * Usage: php sunu i18n:missing [locale]
     */
    public function missing(?string $locale = null): void
    {
        $locale = $locale ?? $this->manager->getLocale();
        $fallback = $this->manager->getFallbackLocale();

        if (!$this->manager->isLocaleSupported($locale)) {
            echo "Error: Locale '{$locale}' is not supported.\n";
            return;
        }

        $current = $this->manager->all($locale);
        $reference = $this->manager->all($fallback);

        $missing = $this->findMissingKeys($reference, $current);

        echo "Missing translations in '{$locale}' (compared to '{$fallback}'):\n";
        echo str_repeat('=', 50) . "\n\n";

        if (empty($missing)) {
            echo "✓ No missing translations found!\n";
        } else {
            foreach ($missing as $key) {
                echo "  - {$key}\n";
            }
            echo "\nTotal missing: " . count($missing) . "\n";
        }
    }

    /**
     * Synchronize translations (regenerate cache).
     * Usage: php sunu i18n:sync
     */
    public function sync(): void
    {
        echo "Synchronizing translations...\n";

        // Clear all caches
        $this->manager->clearCache();

        // Reload all supported locales
        foreach ($this->manager->getSupportedLocales() as $locale) {
            echo  "Loading {$locale}... ";
            $translations = $this->manager->all($locale);
            $count = $this->countKeys($translations);
            echo "✓ ({$count} keys)\n";
        }

        echo "\n✓ Synchronization complete!\n";
    }

    /**
     * Export translations to JSON file.
     * Usage: php sunu i18n:export [locale] [output_file]
     */
    public function export(string $locale, ?string $outputFile = null): void
    {
        if (!$this->manager->isLocaleSupported($locale)) {
            echo "Error: Locale '{$locale}' is not supported.\n";
            return;
        }

        $translations = $this->manager->all($locale);
        $outputFile = $outputFile ?? "export_{$locale}_" . date('Y-m-d_His') . ".json";

        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($outputFile, $json);

        $count = $this->countKeys($translations);
        echo "✓ Exported {$count} keys to: {$outputFile}\n";
    }

    /**
     * Import translations from JSON file.
     * Usage: php sunu i18n:import [locale] [input_file]
     */
    public function import(string $locale, string $inputFile): void
    {
        if (!file_exists($inputFile)) {
            echo "Error: File '{$inputFile}' not found.\n";
            return;
        }

        $content = file_get_contents($inputFile);
        $translations = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Error: Invalid JSON file.\n";
            return;
        }

        // Save as override
        $basePath = dirname(dirname(dirname(__DIR__)));
        $overridePath = $basePath . "/storage/i18n/overrides";

        if (!is_dir($overridePath)) {
            mkdir($overridePath, 0755, true);
        }

        $overrideFile = "{$overridePath}/{$locale}.json";
        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($overrideFile, $json);

        // Clear cache for this locale
        $this->manager->clearCache($locale);

        $count = $this->countKeys($translations);
        echo "✓ Imported {$count} keys for locale '{$locale}' as override.\n";
        echo "  Saved to: {$overrideFile}\n";
    }

    /**
     * Clear translation cache.
     * Usage: php sunu i18n:cache:clear [locale]
     */
    public function clearCache(?string $locale = null): void
    {
        if ($locale) {
            $this->manager->clearCache($locale);
            echo "✓ Cache cleared for locale: {$locale}\n";
        } else {
            $this->manager->clearCache();
            echo "✓ All translation caches cleared.\n";
        }
    }

    /**
     * Show cache statistics.
     * Usage: php sunu i18n:cache:stats
     */
    public function cacheStats(): void
    {
        $stats = $this->manager->getCacheStats();

        echo "Translation Cache Statistics:\n";
        echo str_repeat('=', 50) . "\n\n";

        echo "Total cached locales: {$stats['total_cached']}\n";
        echo "Cache size: " . $this->formatBytes($stats['cache_size']) . "\n\n";

        if (!empty($stats['locales'])) {
            echo "Cached locales:\n";
            foreach ($stats['locales'] as $locale) {
                echo "  - {$locale['locale']}: " . $this->formatBytes($locale['size'])
                    . " (modified: {$locale['modified']})\n";
            }
        }
    }

    /**
     * Print translation keys recursively.
     */
    protected function printKeys(array $translations, string $prefix = ''): void
    {
        foreach ($translations as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->printKeys($value, $fullKey);
            } else {
                echo "  {$fullKey} => {$value}\n";
            }
        }
    }

    /**
     * Count total translation keys.
     */
    protected function countKeys(array $translations): int
    {
        $count = 0;

        foreach ($translations as $value) {
            if (is_array($value)) {
                $count += $this->countKeys($value);
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Find missing keys by comparing two translation arrays.
     */
    protected function findMissingKeys(array $reference, array $current, string $prefix = ''): array
    {
        $missing = [];

        foreach ($reference as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $currentValue = $current[$key] ?? [];
                $missing = array_merge(
                    $missing,
                    $this->findMissingKeys($value, is_array($currentValue) ? $currentValue : [], $fullKey)
                );
            } else {
                if (!isset($current[$key])) {
                    $missing[] = $fullKey;
                }
            }
        }

        return $missing;
    }

    /**
     * Format bytes to human-readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
