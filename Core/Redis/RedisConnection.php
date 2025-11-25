<?php

namespace App\Core\Redis;

use Redis;
use Predis\Client as PredisClient;
use Exception;

/**
 * Class RedisConnection
 * 
 * Smart Redis wrapper that auto-detects and uses:
 * 1. PHP Redis extension (faster, native) if available
 * 2. Predis library (Composer) as fallback
 * 
 * Provides unified API regardless of underlying implementation.
 */
class RedisConnection
{
    private static ?RedisConnection $instance = null;
    private $redis = null; // Redis extension or PredisClient
    private array $config;
    private bool $usingExtension = false;

    private function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            if (empty($config)) {
                throw new Exception("Redis configuration required for first instantiation");
            }
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    private function connect(): void
    {
        $host = $this->config['host'] ?? '127.0.0.1';
        $port = $this->config['port'] ?? 6379;
        $password = $this->config['password'] ?? null;
        $database = $this->config['database'] ?? 0;
        $prefix = $this->config['prefix'] ?? '';
        $timeout = $this->config['timeout'] ?? 2.0;

        // Try PHP Redis extension first (faster)
        if (extension_loaded('redis')) {
            try {
                $this->redis = new Redis();
                $connected = @$this->redis->connect($host, $port, $timeout);

                if ($connected) {
                    if ($password) {
                        $this->redis->auth($password);
                    }
                    $this->redis->select($database);

                    if ($prefix) {
                        $this->redis->setOption(Redis::OPT_PREFIX, $prefix);
                    }

                    $this->usingExtension = true;
                    error_log("✅ Using PHP Redis extension (native, faster)");
                    return;
                }
            } catch (\Exception $e) {
                // Fall through to Predis
            }
        }

        // Fallback to Predis library
        if (class_exists('Predis\Client')) {
            $options = [
                'scheme' => 'tcp',
                'host' => $host,
                'port' => $port,
                'database' => $database,
            ];

            if ($password) {
                $options['password'] = $password;
            }

            if ($prefix) {
                $options['prefix'] = $prefix;
            }

            try {
                $this->redis = new PredisClient($options);
                $this->redis->ping();
                $this->usingExtension = false;
                error_log("✅ Using Predis library (Composer)");
                return;
            } catch (\Exception $e) {
                throw new Exception("Failed to connect with Predis: " . $e->getMessage());
            }
        }

        throw new Exception(
            "No Redis client available. Install either:\n" .
                "  1. PHP Redis extension: pecl install redis (recommended, faster)\n" .
                "  2. Predis library: composer require predis/predis"
        );
    }

    public function getRedis()
    {
        // Check connection
        try {
            $this->redis->ping();
        } catch (\Exception $e) {
            $this->connect();
        }

        return $this->redis;
    }

    public function isUsingExtension(): bool
    {
        return $this->usingExtension;
    }

    // Unified API - works with both Redis extension and Predis

    public function lpush(string $key, string $value): int
    {
        if ($this->usingExtension) {
            return $this->redis->lPush($key, $value);
        } else {
            return $this->redis->lpush($key, [$value]);
        }
    }

    public function rpop(string $key): ?string
    {
        $value = $this->redis->rpop($key);
        return $value === false || $value === null ? null : (string)$value;
    }

    public function llen(string $key): int
    {
        return $this->redis->llen($key);
    }

    public function hset(string $key, string $field, string $value): int
    {
        if ($this->usingExtension) {
            return $this->redis->hSet($key, $field, $value);
        } else {
            return $this->redis->hset($key, $field, $value);
        }
    }

    public function hmset(string $key, array $data): bool
    {
        if ($this->usingExtension) {
            return $this->redis->hMSet($key, $data);
        } else {
            $this->redis->hmset($key, $data);
            return true;
        }
    }

    public function hgetall(string $key): array
    {
        $result = $this->redis->hgetall($key);
        return $result ?: [];
    }

    public function delete(string ...$keys): int
    {
        if ($this->usingExtension) {
            return $this->redis->del(...$keys);
        } else {
            return $this->redis->del($keys);
        }
    }

    public function exists(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    public function expire(string $key, int $seconds): bool
    {
        $result = $this->redis->expire($key, $seconds);
        return $result == 1 || $result === true;
    }

    public function incr(string $key): int
    {
        return $this->redis->incr($key);
    }

    public function keys(string $pattern): array
    {
        $result = $this->redis->keys($pattern);
        return $result ?: [];
    }

    public function zadd(string $key, float $score, string $member): int
    {
        if ($this->usingExtension) {
            return $this->redis->zAdd($key, $score, $member);
        } else {
            return $this->redis->zadd($key, [$member => $score]);
        }
    }

    public function zrange(string $key, int $start, int $end, bool $withScores = false): array
    {
        if ($this->usingExtension) {
            return $this->redis->zRange($key, $start, $end, $withScores);
        } else {
            if ($withScores) {
                return $this->redis->zrange($key, $start, $end, 'WITHSCORES') ?: [];
            }
            return $this->redis->zrange($key, $start, $end) ?: [];
        }
    }

    public function zrem(string $key, string $member): int
    {
        if ($this->usingExtension) {
            return $this->redis->zRem($key, $member);
        } else {
            return $this->redis->zrem($key, [$member]);
        }
    }

    public function eval(string $script, array $args = [], int $numKeys = 0)
    {
        return $this->redis->eval($script, $args, $numKeys);
    }

    public function __destruct()
    {
        if ($this->redis) {
            if ($this->usingExtension) {
                $this->redis->close();
            } else {
                $this->redis->disconnect();
            }
        }
    }
}
