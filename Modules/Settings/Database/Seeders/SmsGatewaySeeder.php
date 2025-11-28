<?php

namespace Modules\Settings\Database\Seeders;

use Modules\Settings\Models\SmsGateway;

class SmsGatewaySeeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Orange Côte d\'Ivoire',
                'provider_code' => 'orange_ci',
                'api_url' => 'https://api.orange.com/smsmessaging/v1/outbound',
                'api_key' => '', // To be configured by user
                'api_secret' => '',
                'sender_id' => '',
                'is_active' => false,
                'is_default' => false,
                'priority' => 10,
                'configuration' => json_encode([
                    'auth_type' => 'oauth2',
                    'token_url' => 'https://api.orange.com/oauth/v2/token',
                    'country_code' => '+225'
                ])
            ],
            [
                'name' => 'Infobip',
                'provider_code' => 'infobip',
                'api_url' => 'https://api.infobip.com/sms/2/text/advanced',
                'api_key' => '', // To be configured by user
                'api_secret' => '',
                'sender_id' => '',
                'is_active' => false,
                'is_default' => false,
                'priority' => 5,
                'configuration' => json_encode([
                    'auth_type' => 'api_key',
                    'supports_unicode' => true,
                    'max_recipients' => 1000
                ])
            ]
        ];

        foreach ($gateways as $gateway) {
            // Check if already exists
            $existing = SmsGateway::where('provider_code', $gateway['provider_code'])->first();

            if (!$existing) {
                SmsGateway::create($gateway);
            }
        }

        echo "SMS Gateways seeded successfully.\n";
    }
}
