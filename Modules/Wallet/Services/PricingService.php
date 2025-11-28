<?php

namespace Modules\Wallet\Services;

class PricingService
{
    protected array $pricing = [
        'default' => 0.05, // Default price per SMS
        'country' => [
            'US' => 0.03,
            'FR' => 0.04,
            'GB' => 0.035,
            'DE' => 0.04,
        ],
        'gateway' => [
            'MockGateway' => 0.02,
            'AnotherMockGateway' => 0.03,
        ]
    ];

    public function calculateCost(array $criteria): float
    {
        // Priority: gateway > country > default

        if (isset($criteria['gateway']) && isset($this->pricing['gateway'][$criteria['gateway']])) {
            return $this->pricing['gateway'][$criteria['gateway']];
        }

        if (isset($criteria['country']) && isset($this->pricing['country'][$criteria['country']])) {
            return $this->pricing['country'][$criteria['country']];
        }

        return $this->pricing['default'];
    }

    public function calculateBulkCost(int $count, array $criteria = []): float
    {
        $unitCost = $this->calculateCost($criteria);
        return $unitCost * $count;
    }

    public function setPricing(string $type, string $key, float $price): void
    {
        if ($type === 'country' || $type === 'gateway') {
            $this->pricing[$type][$key] = $price;
        } elseif ($type === 'default') {
            $this->pricing['default'] = $price;
        }
    }

    public function getPricing(string $type = '', string $key = ''): mixed
    {
        if ($type === '' && $key === '') {
            return $this->pricing;
        }

        if ($key === '') {
            return $this->pricing[$type] ?? null;
        }

        return $this->pricing[$type][$key] ?? null;
    }
}
