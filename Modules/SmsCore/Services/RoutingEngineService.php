<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class RoutingEngineService
{
    protected GatewaySelectorService $selector;
    protected array $rules = [];

    public function __construct(GatewaySelectorService $selector)
    {
        $this->selector = $selector;
    }

    public function route(array $message): SmsGatewayInterface
    {
        $criteria = $this->evaluateRules($message);

        $gateway = $this->selector->selectGateway($criteria);

        if ($gateway === null) {
            throw new \RuntimeException('No gateway available for routing');
        }

        return $gateway;
    }

    protected function evaluateRules(array $message): array
    {
        $criteria = [];

        // Check country-specific routing
        if (isset($message['country'])) {
            foreach ($this->rules as $rule) {
                if ($rule['type'] === 'country' && $rule['value'] === $message['country']) {
                    $criteria['gateway'] = $rule['gateway'];
                    return $criteria;
                }
            }
        }

        // Check priority routing
        if (isset($message['priority']) && $message['priority'] === 'high') {
            foreach ($this->rules as $rule) {
                if ($rule['type'] === 'priority' && $rule['value'] === 'high') {
                    $criteria['gateway'] = $rule['gateway'];
                    return $criteria;
                }
            }
        }

        return $criteria;
    }

    public function addRule(string $type, string $value, string $gateway): void
    {
        $this->rules[] = [
            'type' => $type,
            'value' => $value,
            'gateway' => $gateway
        ];
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
