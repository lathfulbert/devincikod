<?php

namespace App\Core\Cache\Drivers;

use App\Core\Cache\CacheInterface;

/**
 * APCuDriver
 * 
 * Driver ultra-rapide en mémoire partagée (opcode cache).
 * Nécessite: extension PHP apcu
 */
class APCuDriver implements CacheInterface
{
    private string $prefix;
    private bool $enabled = false;

    public function __construct(array $config, string $prefix = '')
    {
        $this->prefix = $prefix;

        if (!extension_loaded('apcu')) {
            throw new \RuntimeException("APCu extension is not installed");
        }

        if (!ini_get('apc.enabled')) {
            throw new \RuntimeException("APCu is not enabled in php.ini");
        }

        $this->enabled = true;
    }

    /**
     * Ajoute le préfixe à une clé
     */
    private function prefixKey(string $key): string
    {
        return $this->prefix . $key;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->enabled) {
            return $default;
        }

        $success = false;
        $value = apcu_fetch($this->prefixKey($key), $success);

        return $success ? $value : $default;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $ttl = $ttl ?? 0; // 0 = pas d'expiration

        return apcu_store($this->prefixKey($key), $value, $ttl);
    }

    public function has(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }

        return apcu_exists($this->prefixKey($key));
    }

    public function delete(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }

        return apcu_delete($this->prefixKey($key));
    }

    public function clear(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // Si on a un préfixe, supprimer uniquement les clés avec ce préfixe
        if (!empty($this->prefix)) {
            $iterator = new \APCUIterator('/^' . preg_quote($this->prefix, '/') . '/');

            foreach ($iterator as $item) {
                apcu_delete($item['key']);
            }

            return true;
        }

        // Sinon vider tout APCu (attention!)
        return apcu_clear_cache();
    }

    public function getMultiple(array $keys, mixed $default = null): array
    {
        if (!$this->enabled) {
            return array_fill_keys($keys, $default);
        }

        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), $keys);
        $values = apcu_fetch($prefixedKeys);

        $result = [];
        foreach ($keys as $key) {
            $prefixedKey = $this->prefixKey($key);
            $result[$key] = $values[$prefixedKey] ?? $default;
        }

        return $result;
    }

    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $ttl = $ttl ?? 0;
        $prefixedValues = [];

        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefixKey($key)] = $value;
        }

        $errors = apcu_store($prefixedValues, null, $ttl);

        // apcu_store retourne un tableau des clés qui ont échoué
        return empty($errors);
    }

    public function deleteMultiple(array $keys): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if (empty($keys)) {
            return true;
        }

        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), $keys);

        foreach ($prefixedKeys as $key) {
            apcu_delete($key);
        }

        return true;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        if (!$this->enabled) {
            return false;
        }

        $result = apcu_inc($this->prefixKey($key), $value, $success);

        // Si la clé n'existe pas, l'initialiser
        if (!$success) {
            $this->set($key, $value);
            return $value;
        }

        return $result;
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        if (!$this->enabled) {
            return false;
        }

        $result = apcu_dec($this->prefixKey($key), $value, $success);

        // Si la clé n'existe pas, l'initialiser à 0
        if (!$success) {
            $this->set($key, 0);
            return 0;
        }

        return $result;
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function getStats(): array
    {
        if (!$this->enabled) {
            return [
                'enabled' => false,
                'error' => 'APCu is not enabled'
            ];
        }

        try {
            $cacheInfo = apcu_cache_info(true);
            $smaInfo = apcu_sma_info(true);

            return [
                'enabled' => true,
                'num_entries' => $cacheInfo['num_entries'] ?? 0,
                'memory_size' => $smaInfo['num_seg'] * $smaInfo['seg_size'] ?? 0,
                'memory_available' => $smaInfo['avail_mem'] ?? 0,
                'memory_used_readable' => $this->formatBytes(($smaInfo['num_seg'] * $smaInfo['seg_size'] - $smaInfo['avail_mem']) ?? 0),
                'hit_rate' => $this->calculateHitRate($cacheInfo),
            ];
        } catch (\Exception $e) {
            return [
                'enabled' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calcule le taux de hit
     */
    private function calculateHitRate(array $info): string
    {
        $hits = $info['num_hits'] ?? 0;
        $misses = $info['num_misses'] ?? 0;
        $total = $hits + $misses;

        if ($total === 0) {
            return '0%';
        }

        return round(($hits / $total) * 100, 2) . '%';
    }

    /**
     * Formate les octets en format lisible
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Vérifie si APCu est activé
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
