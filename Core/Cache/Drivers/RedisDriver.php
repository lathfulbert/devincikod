<?php

namespace App\Core\Cache\Drivers;

use App\Core\Cache\CacheInterface;
use Predis\Client;

/**
 * RedisDriver
 * 
 * Driver haute performance utilisant Redis via Predis.
 * Nécessite: composer require predis/predis
 */
class RedisDriver implements CacheInterface
{
    private ?Client $redis = null;
    private string $prefix;
    private bool $connected = false;

    public function __construct(array $config, string $prefix = '')
    {
        $this->prefix = $prefix;

        try {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host' => $config['redis_host'] ?? '127.0.0.1',
                'port' => $config['redis_port'] ?? 6379,
                'password' => $config['redis_password'] ?? null,
                'database' => $config['redis_database'] ?? 0,
            ]);

            // Test de connexion
            $this->redis->ping();
            $this->connected = true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Redis connection failed: {$e->getMessage()}");
        }
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

        $value = $this->redis->get($this->prefixKey($key));

        if ($value === null) {
            return $default;
        }

        return unserialize($value);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->connected) {
            return false;
        }

        $serialized = serialize($value);
        $prefixedKey = $this->prefixKey($key);

        if ($ttl !== null && $ttl > 0) {
            return (bool)$this->redis->setex($prefixedKey, $ttl, $serialized);
        }

        return (bool)$this->redis->set($prefixedKey, $serialized);
    }

    public function has(string $key): bool
    {
        if (!$this->connected) {
            return false;
        }

        return (bool)$this->redis->exists($this->prefixKey($key));
    }

    public function delete(string $key): bool
    {
        if (!$this->connected) {
            return false;
        }

        return (bool)$this->redis->del([$this->prefixKey($key)]);
    }

    public function clear(): bool
    {
        if (!$this->connected) {
            return false;
        }

        // Si on a un préfixe, supprimer uniquement les clés avec ce préfixe
        if (!empty($this->prefix)) {
            $keys = $this->redis->keys($this->prefix . '*');
            if (count($keys) > 0) {
                $this->redis->del($keys);
            }
        } else {
            // Sinon vider toute la base
            $this->redis->flushdb();
        }

        return true;
    }

    public function getMultiple(array $keys, mixed $default = null): array
    {
        if (!$this->connected) {
            return array_fill_keys($keys, $default);
        }

        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), $keys);
        $values = $this->redis->mget($prefixedKeys);

        $result = [];
        foreach ($keys as $i => $key) {
            $result[$key] = $values[$i] !== null ? unserialize($values[$i]) : $default;
        }

        return $result;
    }

    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        if (!$this->connected) {
            return false;
        }

        // Utiliser pipeline pour performances
        $pipe = $this->redis->pipeline();

        foreach ($values as $key => $value) {
            $serialized = serialize($value);
            $prefixedKey = $this->prefixKey($key);

            if ($ttl !== null && $ttl > 0) {
                $pipe->setex($prefixedKey, $ttl, $serialized);
            } else {
                $pipe->set($prefixedKey, $serialized);
            }
        }

        $pipe->execute();
        return true;
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
        $this->redis->del($prefixedKeys);

        return true;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        if (!$this->connected) {
            return false;
        }

        try {
            return (int)$this->redis->incrby($this->prefixKey($key), $value);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        if (!$this->connected) {
            return false;
        }

        try {
            return (int)$this->redis->decrby($this->prefixKey($key), $value);
        } catch (\Exception $e) {
            return false;
        }
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
                'error' => 'Not connected to Redis server'
            ];
        }

        try {
            $info = $this->redis->info();

            return [
                'connected' => true,
                'redis_version' => $info['Server']['redis_version'] ?? 'unknown',
                'used_memory' => $info['Memory']['used_memory_human'] ?? 'unknown',
                'total_keys' => $this->redis->dbsize(),
                'connected_clients' => $info['Clients']['connected_clients'] ?? 0,
                'uptime_days' => isset($info['Server']['uptime_in_days']) ? $info['Server']['uptime_in_days'] : 0,
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie la connexion Redis
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }
}
