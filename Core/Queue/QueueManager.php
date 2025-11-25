<?php

namespace App\Core\Queue;

use App\Core\Queue\Contracts\QueueDriverContract;
use App\Core\Queue\Drivers\DatabaseDriver;
use App\Core\Queue\Drivers\RedisDriver;
use App\Core\Queue\Drivers\FileDriver;

/**
 * Class QueueManager
 * 
 * Central orchestrator for the queue system.
 * Manages queue drivers, job dispatch, and configuration.
 */
class QueueManager
{
    private static ?QueueManager $instance = null;
    private array $config;
    private ?QueueDriverContract $driver = null;

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): QueueManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->loadConfig();
        $this->initializeDriver();
    }

    /**
     * Load queue configuration.
     */
    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../../config/queue.php';

        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            $this->config = $this->getDefaultConfig();
        }
    }

    /**
     * Get default configuration.
     */
    private function getDefaultConfig(): array
    {
        return [
            'driver' => 'database',
            'connections' => [
                'database' => [
                    'table' => 'jobs',
                    'failed_table' => 'failed_jobs',
                ],
                'redis' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'password' => null,
                    'database' => 0,
                ],
                'file' => [
                    'path' => 'storage/queue',
                ],
            ],
            'worker' => [
                'sleep' => 3,
                'max_tries' => 3,
                'timeout' => 60,
                'memory_limit' => 128,
            ],
        ];
    }

    /**
     * Initialize the queue driver based on configuration.
     */
    private function initializeDriver(): void
    {
        $driver = $this->config['driver'] ?? 'database';

        $this->driver = match ($driver) {
            'redis' => new RedisDriver($this->config),
            'file' => new FileDriver($this->config),
            default => new DatabaseDriver($this->config),
        };
    }

    /**
     * Push a job onto the queue.
     *
     * @param string $jobClass Fully qualified job class name
     * @param array $data Job data payload
     * @param string $queue Queue name
     * @return bool Success status
     */
    public function push(string $jobClass, array $data = [], string $queue = 'default'): bool
    {
        $payload = JobSerializer::serialize($jobClass, $data);
        $payload['available_at'] = time();

        return $this->driver->push($queue, $payload);
    }

    /**
     * Push a delayed job onto the queue.
     *
     * @param string $jobClass Fully qualified job class name
     * @param array $data Job data payload
     * @param string $queue Queue name
     * @param int $delay Delay in seconds
     * @return bool Success status
     */
    public function pushDelayed(string $jobClass, array $data, string $queue, int $delay): bool
    {
        $payload = JobSerializer::serialize($jobClass, $data);
        $payload['available_at'] = time() + $delay;

        return $this->driver->push($queue, $payload);
    }

    /**
     * Get the active queue driver.
     */
    public function getDriver(): QueueDriverContract
    {
        return $this->driver;
    }

    /**
     * Get the queue configuration.
     */
    public function getConfig(string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Get the size of a queue.
     */
    public function size(string $queue = 'default'): int
    {
        return $this->driver->size($queue);
    }

    /**
     * Clear a queue.
     */
    public function clear(string $queue = 'default'): bool
    {
        return $this->driver->clear($queue);
    }
}
