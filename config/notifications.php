<?php

/**
 * Notifications Configuration
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    */
    'default_channel' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Channels Configuration
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'email' => [
            'default_provider' => env('MAIL_PROVIDER', 'smtp'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@sunuframework.com'),
                'name' => env('MAIL_FROM_NAME', 'SunuFramework'),
            ],
        ],
        'sms' => [
            'default_provider' => env('SMS_PROVIDER', 'twilio'),
        ],
        'push' => [
            'default_provider' => env('PUSH_PROVIDER', 'fcm'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Credentials
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'smtp' => [
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME'),
        ],

        'sendgrid' => [
            'api_key' => env('SENDGRID_API_KEY'),
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME'),
        ],

        'mailgun' => [
            'domain' => env('MAILGUN_DOMAIN'),
            'secret' => env('MAILGUN_SECRET'),
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME'),
        ],

        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],

        'fcm' => [
            'server_key' => env('FCM_SERVER_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'max_attempts' => 3,
        'backoff' => [30, 300, 1800], // 30s, 5m, 30m
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'throttle' => [
        'email' => ['rate' => 100, 'per' => 'minute'],
        'sms' => ['rate' => 10, 'per' => 'minute'],
        'push' => ['rate' => 500, 'per' => 'minute'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'redis'),
        'default_queue' => 'notifications',
    ],
];
