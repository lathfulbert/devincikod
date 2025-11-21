<?php

return [
    'name' => getenv('APP_NAME') ?: 'SunuFramework',
    'url' => getenv('APP_URL') ?: '/sunuframework2',
    'debug' => getenv('APP_DEBUG') === 'true',
    'modules' => [
        'Demo' => true,
        'Blog' => true,
        'Auth' => true,
        'RBAC' => true,
        'Admin' => true,
        'I18n' => true
    ],
    'database' => [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'database' => getenv('DB_DATABASE') ?: 'sunuframework2',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],

    /*
    |--------------------------------------------------------------------------
    | Internationalization (I18n) Settings
    |--------------------------------------------------------------------------
    */
    'locale' => 'fr',
    'fallback_locale' => 'en',
    'supported_locales' => ['fr', 'en', 'ar'],

    'i18n' => [
        'cache_enabled' => true,
        'cache_path' => '/storage/cache/i18n',
        'override_path' => '/storage/i18n/overrides',
    ]
];
