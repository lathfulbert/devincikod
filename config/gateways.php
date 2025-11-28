<?php

/**
 * SMS Gateway Configuration
 * 
 * Configure your SMS gateways here
 */

return [
    'infobip' => [
        'class' => \Modules\SmsCore\Gateways\InfobipGateway::class,
        'api_key' => env('INFOBIP_API_KEY', ''),
        'base_url' => env('INFOBIP_BASE_URL', 'https://api.infobip.com'),
        'sender_id' => env('INFOBIP_SENDER_ID', 'InfoSMS'),
    ],

    'orange' => [
        'class' => \Modules\SmsCore\Gateways\OrangeSmsGateway::class,
        'client_id' => env('ORANGE_CLIENT_ID', ''),
        'client_secret' => env('ORANGE_CLIENT_SECRET', ''),
        'base_url' => env('ORANGE_BASE_URL', 'https://api.orange.com'),
    ],

    'mock' => [
        'class' => \Modules\SmsCore\Services\MockGateway::class,
    ],
];
