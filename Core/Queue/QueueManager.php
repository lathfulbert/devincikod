<?php

namespace App\Core\Queue;

use App\Core\Queue\Contracts\QueueDriverContract;

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
        $this->driver = $this->createDriver($this->config);
    }

    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../../config/queue.php';
        $this->config = file_exists($configPath) ? require $configPath : $this->getDefaultConfig();
    }

    private function getDefaultConfig(): array
    {
        return [
            'default' => 'database',
            'connections' => [
                'database' => [
                    'driver' => 'database',
                    'table' => 'jobs',
                    'failed_table' => 'failed_jobs',
                ],
            ],
        ];
    }

    private function createDriver(array $config): QueueDriverContract
    {
        $connectionName = $config['default'] ?? 'database';
        $connectionConfig = $config['connections'][$connectionName] ?? [];
        $driverType = $connectionConfig['driver'] ?? 'database';

        switch ($driverType) {
            case 'database':
                return new Drivers\DatabaseDriver($connectionConfig);

            case 'redis':
                return new Drivers\RedisDriver($connectionConfig);

            default:
                throw new \Exception("Unsupported queue driver: {$driverType}");
        }
    }

    public function push(string $jobClass, array $data = [], string $queue = 'default'): bool
    {
        $payload = JobSerializer::serialize($jobClass, $data);
        $payload['available_at'] = time();

        return $this->driver->push($queue, ['payload' => $payload]);
    }

    public function pushDelayed(string $jobClass, array $data, string $queue, int $delay): bool
    {
        $payload = JobSerializer::serialize($jobClass, $data);
        $payload['available_at'] = time() + $delay;

        return $this->driver->push($queue, ['payload' => $payload]);
    }

    public function getDriver(): QueueDriverContract
    {
        return $this->driver;
    }

    public function getConfig(string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    public function size(string $queue = 'default'): int
    {
        return $this->driver->size($queue);
    }

    public function clear(string $queue = 'default'): bool
    {
        $this->driver->clear($queue);
        return true;
    }
}
