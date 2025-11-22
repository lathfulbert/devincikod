<?php

namespace App\Core\Logging;

use App\Core\Logging\Handlers\FileHandler;
use App\Core\Logging\Handlers\DailyFileHandler;
use App\Core\Logging\Handlers\DatabaseHandler;
use App\Core\Logging\Handlers\NullHandler;

class LogManager
{
    protected array $channels = [];
    protected array $config;
    protected string $defaultChannel;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->defaultChannel = $config['default'] ?? 'single';
    }

    /**
     * Get a logger instance for a channel.
     */
    public function channel(?string $name = null): LoggerInterface
    {
        $name = $name ?: $this->defaultChannel;

        if (!isset($this->channels[$name])) {
            $this->channels[$name] = $this->createChannel($name);
        }

        return $this->channels[$name];
    }

    /**
     * Create a logger channel based on configuration.
     */
    protected function createChannel(string $name): LoggerInterface
    {
        $channelConfig = $this->config['channels'][$name] ?? null;

        if (!$channelConfig) {
            throw new \InvalidArgumentException("Log channel [{$name}] not configured.");
        }

        $driver = $channelConfig['driver'] ?? 'single';
        $level = $this->parseLevel($channelConfig['level'] ?? 'debug');

        $handlers = [];

        switch ($driver) {
            case 'single':
                $handlers[] = new FileHandler(
                    $channelConfig['path'] ?? storage_path('logs/app.log'),
                    $level
                );
                break;

            case 'daily':
                $handlers[] = new DailyFileHandler(
                    $channelConfig['path'] ?? storage_path('logs/app.log'),
                    $channelConfig['days'] ?? 7,
                    $level
                );
                break;

            case 'database':
                $handlers[] = new DatabaseHandler(
                    $channelConfig['table'] ?? 'logs',
                    $level
                );
                break;

            case 'null':
                $handlers[] = new NullHandler();
                break;

            default:
                throw new \InvalidArgumentException("Unsupported log driver [{$driver}]");
        }

        return new Logger($name, $handlers);
    }

    /**
     * Parse log level string to integer.
     */
    protected function parseLevel(string $level): int
    {
        $levels = [
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            'alert' => Logger::ALERT,
            'emergency' => Logger::EMERGENCY,
        ];

        return $levels[strtolower($level)] ?? Logger::DEBUG;
    }

    /**
     * Dynamically call the default channel instance.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->channel()->$method(...$parameters);
    }
}
