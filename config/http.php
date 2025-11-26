<?php

/**
 * HTTP Client Configuration
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout in seconds for HTTP requests.
    |
    */
    'timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Enable or disable SSL certificate verification.
    |
    */
    'verify' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Headers
    |--------------------------------------------------------------------------
    |
    | Headers to include in all requests by default.
    |
    */
    'headers' => [
        'User-Agent' => 'SunuFramework/1.0',
        'Accept' => 'application/json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Default retry configuration for failed requests.
    |
    */
    'retry' => [
        'times' => 0,
        'sleep' => 0, // milliseconds
    ],
];
