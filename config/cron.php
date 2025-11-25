<?php

/**
 * Cron Scheduler Configuration
 * 
 * Configure the cron scheduler settings.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Timezone
    |--------------------------------------------------------------------------
    |
    | The timezone to use for scheduling tasks.
    |
    */
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store to use for mutex locks (preventing overlapping tasks).
    | Supported: "file", "database", "redis"
    |
    */
    'cache_store' => env('CRON_CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Whether to log cron task executions to the database.
    |
    */
    'logging' => [
        'enabled' => true,
        'table' => 'cron_logs',
        'retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Binary Path
    |--------------------------------------------------------------------------
    |
    | Path to the PHP binary. Used when spawning background processes.
    |
    */
    'php_binary' => env('PHP_BINARY', 'php'),
];
