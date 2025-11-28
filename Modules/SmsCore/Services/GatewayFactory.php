<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class GatewayFactory
{
    protected array $configurations = [];

    public function registerConfiguration(string $name, array $config): void
    {
        $this->configurations[$name] = $config;
    }

    public function create(string $name): ?SmsGatewayInterface
    {
        if (!isset($this->configurations[$name])) {
            throw new \InvalidArgumentException("Gateway configuration '$name' not found");
        }

        $config = $this->configurations[$name];
        $class = $config['class'] ?? null;

        if (!$class || !class_exists($class)) {
            throw new \RuntimeException("Gateway class not found: $class");
        }

        return new $class($config);
    }

    public function getAvailableGateways(): array
    {
        return array_keys($this->configurations);
    }
}
