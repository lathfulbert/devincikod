<?php

return [
    // Default MFA settings
    'mfa' => [
        'enabled' => true,
        'required' => false, // If true, all users MUST enable MFA
        'methods' => [
            'totp' => true,
            'sms' => true,
            'email' => true,
        ],
    ],

    // Password policy
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => false,
        'expiry_days' => null, // null = never expires
    ],

    // Session settings
    'session' => [
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
    ],

    // JWT settings
    'jwt' => [
        'secret' => env('JWT_SECRET', 'change_me_in_production'),
        'access_token_ttl' => 3600, // 1 hour
        'refresh_token_ttl' => 86400 * 30, // 30 days
    ],

    // Rate limiting
    'rate_limiting' => [
        'max_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
    ],
];
