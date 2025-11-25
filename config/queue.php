<?php

/**
 * Queue Configuration
 * 
 * Configure the queue system drivers, connections, and worker settings.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "database", "redis", "file"
    |
    */
    'driver' => env('QUEUE_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Configuration for each queue driver.
    |
    */
    'connections' => [
        'database' => [
            'table' => 'jobs',
            'failed_table' => 'failed_jobs',
        ],

        'redis' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_QUEUE_DB', 0),
            'queue' => 'default',
        ],

        'file' => [
            'path' => 'storage/queue',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Worker Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for queue workers.
    |
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
    |
    | Settings for failed job management.
    |
    */
    'failed' => [
        'retention_days' => 30,  // Auto-delete failed jobs after X days
    ],
];
