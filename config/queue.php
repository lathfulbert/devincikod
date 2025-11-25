<?php

/**
 * Queue Configuration
 * 
 * Configure the queue system drivers, connections, and worker settings.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection
    |--------------------------------------------------------------------------
    */
    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */
    'connections' => [
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'failed_table' => 'failed_jobs',
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_QUEUE_DB', 0),
            'prefix' => env('REDIS_QUEUE_PREFIX', 'queue:'),
            'timeout' => 2.0,
        ],

        'file' => [
            'driver' => 'file',
            'path' => 'storage/queue',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Worker Settings
    |--------------------------------------------------------------------------
    */
    'worker' => [
        'sleep' => 3,            // Sleep seconds when queue is empty
        'max_tries' => 3,        // Default maximum attempts
        'timeout' => 60,         // Default timeout (seconds)
        'memory_limit' => 128,   // Memory limit (MB)
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Jobs
    |--------------------------------------------------------------------------
    */
    'failed' => [
        'retention_days' => 30,  // Auto-delete failed jobs after X days
    ],
];
