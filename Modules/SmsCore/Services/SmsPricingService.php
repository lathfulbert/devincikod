<?php

namespace Modules\SmsCore\Services;

use Modules\Settings\Models\Setting;

class SmsPricingService
{
    protected array $pricingGrid = [];

    public function __construct()
    {
        $this->loadPricingGrid();
    }

    /**
     * Load pricing grid from settings
     */
    protected function loadPricingGrid(): void
    {
        $this->pricingGrid = Setting::get('sms_pricing_grid', []);

        // Default fallback if empty
        if (empty($this->pricingGrid)) {
            $this->pricingGrid = [
                'default' => [
                    'price' => 15,
                    'currency' => 'XOF'
                ]
            ];
        }
    }

    /**
     * Calculate cost for a single SMS
     */
    public function calculateCost(string $recipient, string $gateway, string $type = 'text'): array
    {
        // 1. Detect Country and Operator (Simplified logic for now)
        $countryCode = $this->detectCountry($recipient);
        $operator = $this->detectOperator($recipient);

        // 2. Get Unit Price
        $unitPrice = $this->getUnitPrice($countryCode, $operator, $gateway, $type);
        $currency = $this->getCurrency($countryCode);

        return [
            'unit_cost' => $unitPrice,
            'currency' => $currency,
            'country_code' => $countryCode,
            'operator' => $operator
        ];
    }

    /**
     * Get unit price based on hierarchy:
     * Country > Operator > Gateway > Default
     */
    protected function getUnitPrice(string $country, string $operator, string $gateway, string $type): float
    {
        // Check Country Specifics
        if (isset($this->pricingGrid[$country])) {
            $countryGrid = $this->pricingGrid[$country];

            // Check Operator
            if (isset($countryGrid['networks'][$operator])) {
                return (float) $countryGrid['networks'][$operator];
            }

            // Check Gateway override for country
            if (isset($countryGrid['gateways'][$gateway])) {
                return (float) $countryGrid['gateways'][$gateway];
            }

            // Country Default
            if (isset($countryGrid['default'])) {
                return (float) $countryGrid['default'];
            }
        }

        // Global Default
        return (float) ($this->pricingGrid['default']['price'] ?? 15);
    }

    protected function getCurrency(string $country): string
    {
        if (isset($this->pricingGrid[$country]['currency'])) {
            return $this->pricingGrid[$country]['currency'];
        }
        return $this->pricingGrid['default']['currency'] ?? 'XOF';
    }

    /**
     * Detect country code from MSISDN
     * @todo Implement real detection logic (libphonenumber)
     */
    protected function detectCountry(string $msisdn): string
    {
        if (str_starts_with($msisdn, '225') || str_starts_with($msisdn, '+225')) return 'CI';
        if (str_starts_with($msisdn, '33') || str_starts_with($msisdn, '+33')) return 'FR';
        if (str_starts_with($msisdn, '221') || str_starts_with($msisdn, '+221')) return 'SN';
        return 'UNKNOWN';
    }

    /**
     * Detect operator from MSISDN
     * @todo Implement real detection logic
     */
    protected function detectOperator(string $msisdn): string
    {
        // Simple mock logic for CI
        if (str_contains($msisdn, '07') || str_contains($msisdn, '08') || str_contains($msisdn, '09')) return 'orange';
        if (str_contains($msisdn, '05') || str_contains($msisdn, '06')) return 'mtn';
        if (str_contains($msisdn, '01') || str_contains($msisdn, '02')) return 'moov';
        return 'unknown';
    }

    /**
     * Calculate number of segments
     * GSM 03.38: 160 chars (1), 306 (2), 459 (3)... (153 chars per segment for multi-part)
     * Unicode: 70 chars (1), 134 (2), 201 (3)... (67 chars per segment for multi-part)
     */
    public function calculateSegments(string $message): int
    {
        $isUnicode = mb_strlen($message) != strlen($message); // Simple check, can be improved
        $length = mb_strlen($message);

        if ($isUnicode) {
            if ($length <= 70) return 1;
            return ceil($length / 67);
        } else {
            if ($length <= 160) return 1;
            return ceil($length / 153);
        }
    }
}
