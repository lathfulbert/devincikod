<?php

namespace App\Core\Cache;

use App\Core\Cache\Drivers\FilesystemDriver;
use App\Core\Cache\Drivers\RedisDriver;
use App\Core\Cache\Drivers\MemcachedDriver;
use App\Core\Cache\Drivers\APCuDriver;
use App\Core\Database\Database;

/**
 * CacheManager
 * 
 * Gestionnaire central du système de cache.
 * Pattern Singleton avec factory pour instancier le driver approprié.
 */
class CacheManager implements CacheInterface
{
    private static ?CacheManager $instance = null;
    private ?CacheInterface $driver = null;
    private array $config = [];
    private string $prefix = '';

    private function __construct()
    {
        $this->loadConfig();
        $this->initializeDriver();
    }

    /**
     * Récupère l'instance unique du CacheManager
     */
    public static function getInstance(): CacheManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Charge la configuration depuis la base de données
     */
    private function loadConfig(): void
    {
        try {
            $db = Database::getInstance();
            $result = $db->query("SELECT * FROM cache_config LIMIT 1");

            if ($result && count($result) > 0) {
                $this->config = $result[0];
                $this->prefix = $this->config['prefix'] ?? '';
            } else {
                // Configuration par défaut si pas en base
                $this->config = $this->getDefaultConfig();
            }
        } catch (\Exception $e) {
            // Si erreur (table n'existe pas encore), utiliser config par défaut
            $this->config = $this->getDefaultConfig();
        }
    }

    /**
     * Configuration par défaut
     */
    private function getDefaultConfig(): array
    {
        return [
            'driver' => 'filesystem',
            'enabled' => 1,
            'prefix' => 'cache_',
            'default_ttl' => 3600,
            'filesystem_path' => 'storage/cache',
            'redis_host' => '127.0.0.1',
            'redis_port' => 6379,
            'redis_password' => null,
            'redis_database' => 0,
            'memcached_servers' => json_encode([['host' => '127.0.0.1', 'port' => 11211]]),
            'apcu_enabled' => extension_loaded('apcu')
        ];
    }

    /**
     * Initialise le driver de cache selon la configuration
     */
    private function initializeDriver(): void
    {
        if (!$this->config['enabled']) {
            // Cache désactivé, utiliser un driver null (pas d'erreur, juste pas de mise en cache)
            $this->driver = new FilesystemDriver($this->config, $this->prefix);
            return;
        }

        $driverName = $this->config['driver'] ?? 'filesystem';

        try {
            $this->driver = match ($driverName) {
                'redis' => new RedisDriver($this->config, $this->prefix),
                'memcached' => new MemcachedDriver($this->config, $this->prefix),
                'apcu' => new APCuDriver($this->config, $this->prefix),
                default => new FilesystemDriver($this->config, $this->prefix)
            };
        } catch (\Exception $e) {
            // Fallback sur Filesystem en cas d'erreur
            error_log("Cache driver '$driverName' failed to initialize: {$e->getMessage()}. Falling back to filesystem.");
            $this->driver = new FilesystemDriver($this->config, $this->prefix);
        }
    }

    /**
     * Recharge la configuration (utile après modification en admin)
     */
    public function reload(): void
    {
        $this->loadConfig();
        $this->initializeDriver();
    }

    /**
     * Retourne le driver actuel
     */
    public function getDriver(): CacheInterface
    {
        return $this->driver;
    }

    /**
     * Retourne le nom du driver actif
     */
    public function getDriverName(): string
    {
        return $this->config['driver'] ?? 'filesystem';
    }

    // ==================== Méthodes CacheInterface (proxy vers le driver) ====================

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver->get($key, $default);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? ($this->config['default_ttl'] ?? 3600);
        return $this->driver->set($key, $value, $ttl);
    }

    public function has(string $key): bool
    {
        return $this->driver->has($key);
    }

    public function delete(string $key): bool
    {
        return $this->driver->delete($key);
    }

    public function clear(): bool
    {
        return $this->driver->clear();
    }

    public function getMultiple(array $keys, mixed $default = null): array
    {
        return $this->driver->getMultiple($keys, $default);
    }

    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? ($this->config['default_ttl'] ?? 3600);
        return $this->driver->setMultiple($values, $ttl);
    }

    public function deleteMultiple(array $keys): bool
    {
        return $this->driver->deleteMultiple($keys);
    }

    public function increment(string $key, int $value = 1): int|false
    {
        return $this->driver->increment($key, $value);
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        return $this->driver->decrement($key, $value);
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? ($this->config['default_ttl'] ?? 3600);
        return $this->driver->remember($key, $callback, $ttl);
    }

    public function getStats(): array
    {
        return array_merge([
            'driver' => $this->getDriverName(),
            'enabled' => $this->config['enabled'] ?? false,
            'prefix' => $this->prefix,
        ], $this->driver->getStats());
    }
}
