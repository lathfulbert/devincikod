<?php

namespace App\Core\Cache\Drivers;

use App\Core\Cache\CacheInterface;
use Memcached;

/**
 * MemcachedDriver
 * 
 * Driver basé sur l'extension PECL Memcached.
 * Nécessite: extension PHP memcached
 */
class MemcachedDriver implements CacheInterface
{
    private ?Memcached $memcached = null;
    private string $prefix;
    private bool $connected = false;

    public function __construct(array $config, string $prefix = '')
    {
        $this->prefix = $prefix;

        if (!extension_loaded('memcached')) {
            throw new \RuntimeException("Memcached extension is not installed");
        }

        try {
            $this->memcached = new Memcached();

            // Configuration
            $this->memcached->setOption(Memcached::OPT_COMPRESSION, true);
            $this->memcached->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_PHP);
            $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

            // Ajouter les serveurs
            $servers = $this->parseServers($config['memcached_servers'] ?? '');

            if (empty($servers)) {
                $servers = [['127.0.0.1', 11211]];
            }

            foreach ($servers as $server) {
                $this->memcached->addServer($server[0], $server[1]);
            }

            // Test de connexion
            $this->memcached->set('test_connection', 1, 1);
            $this->connected = true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Memcached connection failed: {$e->getMessage()}");
        }
    }

    /**
     * Parse la configuration des serveurs depuis JSON
     */
    private function parseServers(string $serversJson): array
    {
        if (empty($serversJson)) {
            return [];
        }

        $servers = json_decode($serversJson, true);
        if (!is_array($servers)) {
            return [];
        }

        $result = [];
        foreach ($servers as $server) {
            $result[] = [
                $server['host'] ?? '127.0.0.1',
                $server['port'] ?? 11211
            ];
        }

        return $result;
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
        if (!$this->connected) {
            return $default;
        }

        $value = $this->memcached->get($this->prefixKey($key));

        // Memcached retourne false pour clé inexistante et erreurs
        if ($value === false && $this->memcached->getResultCode() !== Memcached::RES_SUCCESS) {
            return $default;
        }

        return $value;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->connected) {
            return false;
        }

        $ttl = $ttl ?? 0; // 0 = pas d'expiration dans Memcached

        return $this->memcached->set($this->prefixKey($key), $value, $ttl);
    }

    public function has(string $key): bool
    {
        if (!$this->connected) {
            return false;
        }

        $this->memcached->get($this->prefixKey($key));
        return $this->memcached->getResultCode() === Memcached::RES_SUCCESS;
    }

    public function delete(string $key): bool
    {
        if (!$this->connected) {
            return false;
        }

        return $this->memcached->delete($this->prefixKey($key));
    }

    public function clear(): bool
    {
        if (!$this->connected) {
            return false;
        }

        // Memcached n'a pas de moyen natif de supprimer par préfixe
        // On flush tout (attention dans environnements partagés)
        return $this->memcached->flush();
    }

    public function getMultiple(array $keys, mixed $default = null): array
    {
        if (!$this->connected) {
            return array_fill_keys($keys, $default);
        }

        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), $keys);
        $values = $this->memcached->getMulti($prefixedKeys);

        $result = [];
        foreach ($keys as $key) {
            $prefixedKey = $this->prefixKey($key);
            $result[$key] = $values[$prefixedKey] ?? $default;
        }

        return $result;
    }

    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        if (!$this->connected) {
            return false;
        }

        $ttl = $ttl ?? 0;
        $prefixedValues = [];

        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefixKey($key)] = $value;
        }

        return $this->memcached->setMulti($prefixedValues, $ttl);
    }

    public function deleteMultiple(array $keys): bool
    {
        if (!$this->connected) {
            return false;
        }

        if (empty($keys)) {
            return true;
        }

        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), $keys);
        $this->memcached->deleteMulti($prefixedKeys);

        return true;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        if (!$this->connected) {
            return false;
        }

        $result = $this->memcached->increment($this->prefixKey($key), $value);

        // Si la clé n'existe pas, l'initialiser à $value
        if ($result === false) {
            $this->set($key, $value);
            return $value;
        }

        return (int)$result;
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        if (!$this->connected) {
            return false;
        }

        $result = $this->memcached->decrement($this->prefixKey($key), $value);

        // Si la clé n'existe pas, l'initialiser à 0
        if ($result === false) {
            $this->set($key, 0);
            return 0;
        }

        return (int)$result;
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
        if (!$this->connected) {
            return [
                'connected' => false,
                'error' => 'Not connected to Memcached server'
            ];
        }

        try {
            $stats = $this->memcached->getStats();

            $totalItems = 0;
            $totalBytes = 0;
            $serverCount = 0;

            foreach ($stats as $server => $data) {
                if ($data === false) {
                    continue;
                }

                $serverCount++;
                $totalItems += $data['curr_items'] ?? 0;
                $totalBytes += $data['bytes'] ?? 0;
            }

            return [
                'connected' => true,
                'servers' => $serverCount,
                'total_items' => $totalItems,
                'total_bytes' => $totalBytes,
                'total_size_readable' => $this->formatBytes($totalBytes),
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
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
     * Vérifie la connexion Memcached
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }
}
