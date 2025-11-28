<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class GatewaySelectorService
{
    /** @var SmsGatewayInterface[] */
    protected array $gateways = [];
    protected string $selectionStrategy = 'round_robin'; // round_robin, cheapest, fastest, random
    protected int $currentIndex = 0;

    public function registerGateway(SmsGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getName()] = $gateway;
    }

    public function selectGateway(array $criteria = []): ?SmsGatewayInterface
    {
        if (empty($this->gateways)) {
            return null;
        }

        // If specific gateway requested
        if (isset($criteria['gateway'])) {
            return $this->gateways[$criteria['gateway']] ?? null;
        }

        return match ($this->selectionStrategy) {
            'round_robin' => $this->selectRoundRobin(),
            'random' => $this->selectRandom(),
            default => $this->selectFirst()
        };
    }

    protected function selectRoundRobin(): SmsGatewayInterface
    {
        $gateways = array_values($this->gateways);
        $gateway = $gateways[$this->currentIndex];

        $this->currentIndex = ($this->currentIndex + 1) % count($gateways);

        return $gateway;
    }

    protected function selectRandom(): SmsGatewayInterface
    {
        $gateways = array_values($this->gateways);
        return $gateways[array_rand($gateways)];
    }

    protected function selectFirst(): SmsGatewayInterface
    {
        return reset($this->gateways);
    }

    public function setStrategy(string $strategy): void
    {
        $this->selectionStrategy = $strategy;
    }

    public function getGateways(): array
    {
        return array_keys($this->gateways);
    }
}
