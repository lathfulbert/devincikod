<?php

return [
    'database' => [
        'enabled' => true,
        'exclude_tables' => ['logs', 'sessions'],
        'rotation_days' => 7,
        'compression' => true, // gzip
        'encryption' => false, // AES-256 (future)
    ],
    'files' => [
        'enabled' => true,
        'paths' => [
            'storage/uploads',
            'storage/app/public',
        ],
        'exclude' => [
            'storage/cache',
            'storage/logs',
            'storage/framework',
        ],
    ],
    'storage' => [
        'default' => 'local',
        'drivers' => [
            'local' => [
                'driver' => 'local',
                'path' => 'storage/backups',
            ],
            's3' => [
                'driver' => 's3',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'path' => 'backups',
            ],
        ],
    ],
    'monitoring' => [
        'enabled' => true,
        'alerts' => [
            'disk_usage_threshold' => 90, // percent
            'cpu_usage_threshold' => 85, // percent
            'memory_usage_threshold' => 90, // percent
        ],
        'services' => [
            'cron',
            'queue',
            'cache',
            'mail',
        ],
    ],
];
