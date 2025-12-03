<?php

/**
 * Configuration des gateways email
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Gateway par défaut
    |--------------------------------------------------------------------------
    */
    'default' => env('MAIL_GATEWAY', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Gateways disponibles
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'mock' => [
            'driver' => 'mock',
            'enabled' => env('APP_ENV') === 'local',
        ],

        'smtp' => [
            'driver' => 'smtp',
            'host' => env('MAIL_HOST', 'localhost'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_email' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'from_name' => env('MAIL_FROM_NAME', 'SunuFramework'),
            'enabled' => true,
        ],

        'sendgrid' => [
            'driver' => 'sendgrid',
            'api_key' => env('SENDGRID_API_KEY'),
            'from_email' => env('SENDGRID_FROM_ADDRESS'),
            'from_name' => env('SENDGRID_FROM_NAME'),
            'enabled' => !empty(env('SENDGRID_API_KEY')),
        ],

        'twilio' => [
            'driver' => 'twilio',
            'api_key' => env('TWILIO_EMAIL_API_KEY'),
            'from_email' => env('TWILIO_FROM_ADDRESS'),
            'from_name' => env('TWILIO_FROM_NAME'),
            'enabled' => !empty(env('TWILIO_EMAIL_API_KEY')),
        ],

        'infobip' => [
            'driver' => 'infobip',
            'api_key' => env('INFOBIP_EMAIL_API_KEY'),
            'base_url' => env('INFOBIP_EMAIL_BASE_URL', 'https://api.infobip.com'),
            'from_email' => env('INFOBIP_FROM_ADDRESS'),
            'from_name' => env('INFOBIP_FROM_NAME'),
            'enabled' => !empty(env('INFOBIP_EMAIL_API_KEY')),
        ],

        'elasticmail' => [
            'driver' => 'elasticmail',
            'api_key' => env('ELASTICMAIL_API_KEY'),
            'from_email' => env('ELASTICMAIL_FROM_ADDRESS'),
            'from_name' => env('ELASTICMAIL_FROM_NAME'),
            'enabled' => !empty(env('ELASTICMAIL_API_KEY')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Coûts par email
    |--------------------------------------------------------------------------
    */
    'pricing' => [
        'default_cost' => 0.001, // 0.001€ par email
        'currency' => 'EUR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking
    |--------------------------------------------------------------------------
    */
    'tracking' => [
        'open_tracking' => true,
        'click_tracking' => true,
        'pixel_url' => env('APP_URL') . '/email/track/open/{messageId}',
        'click_url' => env('APP_URL') . '/email/track/click/{messageId}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_recipients_per_campaign' => 10000,
        'max_recipients_per_bulk' => 1000,
        'rate_limit_per_hour' => 1000,
    ],
];
