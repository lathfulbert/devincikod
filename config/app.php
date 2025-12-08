<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    */
    'env' => getenv('APP_ENV') ?: 'production',

    'name' => getenv('APP_NAME') ?: 'SunuFramework',
    'url' => getenv('APP_URL') ?: null,
    'debug' => getenv('APP_DEBUG') === 'true',
    'key' => getenv('APP_KEY') ?: null,

    /*
    |--------------------------------------------------------------------------
    | Environment-specific Features
    |--------------------------------------------------------------------------
    */
    'show_error_details' => getenv('SHOW_ERROR_DETAILS') === 'true'
        ? true
        : (getenv('APP_ENV') !== 'production'),

    'query_log_enabled' => getenv('QUERY_LOG_ENABLED') === 'true'
        ? true
        : (getenv('APP_ENV') === 'development'),

    'force_https' => getenv('FORCE_HTTPS') === 'true'
        ? true
        : (getenv('APP_ENV') === 'production'),

    'maintenance' => getenv('MAINTENANCE_MODE') === 'true',

    /*
    |--------------------------------------------------------------------------
    | Modules Configuration
    |--------------------------------------------------------------------------
    */
    'modules' => [
        'Demo' => true,
        'Blog' => true,
        'Auth' => true,
        'RBAC' => true,
        'Admin' => true,
        'I18n' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration (Legacy - use config/database.php)
    |--------------------------------------------------------------------------
    */
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
    'locale' => 'en',
    'fallback_locale' => 'en',
    'supported_locales' => ['fr', 'en', 'ar'],

    'i18n' => [
        'cache_enabled' => true,
        'cache_path' => '/storage/cache/i18n',
        'override_path' => '/storage/i18n/overrides',
    ]
];
