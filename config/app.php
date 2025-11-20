<?php

return [
    'name' => getenv('APP_NAME') ?: 'SunuFramework',
    'debug' => getenv('APP_DEBUG') === 'true',
    'modules' => [
        'Demo' => true,
        'Blog' => true,
        'Auth' => true,
        'RBAC' => true
    ],
    'database' => [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'database' => getenv('DB_DATABASE') ?: 'sunuframework2',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]
];
