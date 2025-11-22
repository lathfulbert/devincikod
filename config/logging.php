<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, LathDevinci uses the "daily" channel which writes log files
    | on a daily basis. Other options include "single" and "null".
    |
    | Available Drivers: "single", "daily", "null"
    |
    */

    'channels' => [

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/app.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/app.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'null' => [
            'driver' => 'null',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'logs',
            'level' => 'debug',
        ],

        'emergency' => [
            'driver' => 'single',
            'path' => storage_path('logs/emergency.log'),
            'level' => 'emergency',
        ],

    ],

];
