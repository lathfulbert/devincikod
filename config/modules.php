<?php

/**
 * Module Registration
 * 
 * Register your modules here to make them available to the application
 */

return [
    // Core Modules (existing)
    \Modules\Admin\AdminModule::class,
    \Modules\Auth\AuthModule::class,
    \Modules\RBAC\RBACModule::class,
    \Modules\I18n\I18nModule::class,
    \Modules\Notifications\NotificationsModule::class,
    \Modules\Backup\BackupModule::class,
    \Modules\Settings\SettingsModule::class,

    // SMS Platform Modules (new)
    \Modules\SmsCore\SmsCoreModule::class,
    \Modules\Wallet\WalletModule::class,
];
