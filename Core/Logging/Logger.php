<?php

namespace App\Core\Logging;

use DateTime;

class Logger implements LoggerInterface
{
    protected array $handlers = [];
    protected string $channel;
    protected int $minLevel = 0;

    // PSR-3 Log Levels
    const EMERGENCY = 7;
    const ALERT = 6;
    const CRITICAL = 5;
    const ERROR = 4;
    const WARNING = 3;
    const NOTICE = 2;
    const INFO = 1;
    const DEBUG = 0;

    protected static array $levels = [
        'debug' => self::DEBUG,
        'info' => self::INFO,
        'notice' => self::NOTICE,
        'warning' => self::WARNING,
        'error' => self::ERROR,
        'critical' => self::CRITICAL,
        'alert' => self::ALERT,
        'emergency' => self::EMERGENCY,
    ];

    public function __construct(string $channel = 'app', array $handlers = [])
    {
        $this->channel = $channel;
        $this->handlers = $handlers;
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $levelValue = self::$levels[$level] ?? self::INFO;

        if ($levelValue < $this->minLevel) {
            return;
        }

        $record = $this->createRecord($level, $message, $context);

        foreach ($this->handlers as $handler) {
            $handler->handle($record);
        }
    }

    protected function createRecord(string $level, string $message, array $context): array
    {
        return [
            'message' => $this->interpolate($message, $context),
            'context' => $context,
            'level' => $level,
            'level_value' => self::$levels[$level] ?? self::INFO,
            'channel' => $this->channel,
            'datetime' => new DateTime(),
            'extra' => $this->getExtraData()
        ];
    }

    protected function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    protected function getExtraData(): array
    {
        return [
            'memory' => memory_get_usage(true),
            'file' => $_SERVER['SCRIPT_FILENAME'] ?? null,
            'line' => null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
    }

    public function addHandler(HandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    public function setMinLevel(string $level): self
    {
        $this->minLevel = self::$levels[$level] ?? self::DEBUG;
        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }
}
