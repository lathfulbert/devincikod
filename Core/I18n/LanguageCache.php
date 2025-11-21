<?php

namespace App\Core\I18n;

/**
 * Class LanguageCache
 * 
 * Caching layer for compiled translations to improve performance.
 * Uses file-based caching.
 */
class LanguageCache
{
    protected string $cachePath;
    protected int $ttl;

    public function __construct(string $cachePath, int $ttl = 3600)
    {
        $this->cachePath = rtrim($cachePath, '/');
        $this->ttl = $ttl;

        // Ensure cache directory exists
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Check if cache exists for a locale.
     */
    public function has(string $locale): bool
    {
        $cacheFile = $this->getCacheFilePath($locale);

        if (!file_exists($cacheFile)) {
            return false;
        }

        // Check if cache is expired
        if ($this->ttl > 0 && (time() - filemtime($cacheFile)) > $this->ttl) {
            $this->forget($locale);
            return false;
        }

        return true;
    }

    /**
     * Get cached translations for a locale.
     */
    public function get(string $locale): ?array
    {
        if (!$this->has($locale)) {
            return null;
        }

        $cacheFile = $this->getCacheFilePath($locale);
        $content = file_get_contents($cacheFile);

        return unserialize($content);
    }

    /**
     * Store translations in cache.
     */
    public function set(string $locale, array $translations): void
    {
        $cacheFile = $this->getCacheFilePath($locale);
        file_put_contents($cacheFile, serialize($translations));
    }

    /**
     * Remove cache for a specific locale.
     */
    public function forget(string $locale): void
    {
        $cacheFile = $this->getCacheFilePath($locale);

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Clear all cached translations.
     */
    public function flush(): void
    {
        $files = glob($this->cachePath . '/*.cache');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get the cache file path for a locale.
     */
    protected function getCacheFilePath(string $locale): string
    {
        return $this->cachePath . '/' . $locale . '.cache';
    }

    /**
     * Get cache statistics.
     */
    public function getStats(): array
    {
        $files = glob($this->cachePath . '/*.cache');
        $stats = [
            'total_cached' => count($files),
            'cache_size' => 0,
            'locales' => []
        ];

        foreach ($files as $file) {
            $locale = basename($file, '.cache');
            $size = filesize($file);
            $mtime = filemtime($file);

            $stats['cache_size'] += $size;
            $stats['locales'][] = [
                'locale' => $locale,
                'size' => $size,
                'modified' => date('Y-m-d H:i:s', $mtime)
            ];
        }

        return $stats;
    }
}
