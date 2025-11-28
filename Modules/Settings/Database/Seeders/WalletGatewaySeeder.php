<?php

namespace Modules\Settings\Database\Seeders;

use Modules\Settings\Models\WalletGateway;

class WalletGatewaySeeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'PayDunya',
                'provider_code' => 'paydunya',
                'api_url' => 'https://app.paydunya.com/api/v1',
                'api_key' => '', // To be configured
                'api_secret' => '',
                'merchant_id' => '',
                'is_active' => false,
                'is_default' => false,
                'currency' => 'XOF',
                'transaction_fee' => 2.5,
                'configuration' => json_encode([
                    'mode' => 'live', // live or test
                    'supported_methods' => ['orange_money', 'mtn', 'moov', 'card']
                ])
            ],
            [
                'name' => 'CinetPay',
                'provider_code' => 'cinetpay',
                'api_url' => 'https://api-checkout.cinetpay.com/v2',
                'api_key' => '', // To be configured
                'api_secret' => '',
                'merchant_id' => '',
                'is_active' => false,
                'is_default' => false,
                'currency' => 'XOF',
                'transaction_fee' => 3.0,
                'configuration' => json_encode([
                    'mode' => 'PRODUCTION', // PRODUCTION or TEST
                    'supported_methods' => ['ORANGE_MONEY_CI', 'MOOV_CI', 'MTN_CI', 'WAVE_CI']
                ])
            ],
            [
                'name' => 'Orange Money Côte d\'Ivoire',
                'provider_code' => 'orange_money_ci',
                'api_url' => 'https://api.orange.com/orange-money-webpay/dev/v1',
                'api_key' => '', // To be configured
                'api_secret' => '',
                'merchant_id' => '',
                'is_active' => false,
                'is_default' => false,
                'currency' => 'XOF',
                'transaction_fee' => 1.5,
                'configuration' => json_encode([
                    'auth_type' => 'oauth2',
                    'country_code' => 'CI'
                ])
            ],
            [
                'name' => 'Wave Côte d\'Ivoire',
                'provider_code' => 'wave_ci',
                'api_url' => 'https://api.wave.com/v1',
                'api_key' => '', // To be configured
                'api_secret' => '',
                'merchant_id' => '',
                'is_active' => false,
                'is_default' => false,
                'currency' => 'XOF',
                'transaction_fee' => 1.0,
                'configuration' => json_encode([
                    'country_code' => 'CI',
                    'qr_code_enabled' => true
                ])
            ]
        ];

        foreach ($gateways as $gateway) {
            // Check if already exists
            $existing = WalletGateway::where('provider_code', $gateway['provider_code'])->first();

            if (!$existing) {
                WalletGateway::create($gateway);
            }
        }

        echo "Wallet Gateways seeded successfully.\n";
    }
}
