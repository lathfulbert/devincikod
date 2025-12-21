<?php

use Modules\Settings\Controllers\SettingsController;
use Modules\Settings\Controllers\SiteSettingsController;
use Modules\Settings\Controllers\ThemeSettingsController;
use Modules\Settings\Controllers\ApiSettingsController;
use Modules\Settings\Controllers\MailSettingsController;
use Modules\Settings\Controllers\SmsSettingsController;
use Modules\Settings\Controllers\WalletSettingsController;
use Modules\Settings\Controllers\LanguageController;
use Modules\Settings\Controllers\TranslationController;
use Modules\Settings\Controllers\WebhookController;
use Modules\Settings\Controllers\MaintenanceController;
use Modules\Settings\Controllers\HealthCheckController;
use Modules\Settings\Controllers\LogsController;

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Main Settings Dashboard
$router->get('/admin/settings', [SettingsController::class, 'index'], $authMiddleware)->name('admin.settings.index');

// Site Settings
$router->get('/admin/settings/site', [SiteSettingsController::class, 'index'], $authMiddleware)->name('admin.settings.site');
$router->post('/admin/settings/site/update', [SiteSettingsController::class, 'update'], $authMiddleware)->name('admin.settings.site.update');
$router->post('/admin/settings/site/upload-logo', [SiteSettingsController::class, 'uploadLogo'], $authMiddleware)->name('admin.settings.site.upload_logo');
$router->post('/admin/settings/site/upload-favicon', [SiteSettingsController::class, 'uploadFavicon'], $authMiddleware)->name('admin.settings.site.upload_favicon');

// Theme Settings
$router->get('/admin/settings/theme', [ThemeSettingsController::class, 'index'], $authMiddleware)->name('admin.settings.theme');
$router->post('/admin/settings/theme/update', [ThemeSettingsController::class, 'update'], $authMiddleware)->name('admin.settings.theme.update');
$router->post('/admin/settings/theme/preview', [ThemeSettingsController::class, 'preview'], $authMiddleware)->name('admin.settings.theme.preview');
$router->post('/admin/settings/theme/reset', [ThemeSettingsController::class, 'reset'], $authMiddleware)->name('admin.settings.theme.reset');

// API Settings
$router->get('/admin/settings/api', [ApiSettingsController::class, 'index'], $authMiddleware)->name('admin.settings.api');
$router->post('/admin/settings/api/update', [ApiSettingsController::class, 'update'], $authMiddleware)->name('admin.settings.api.update');
$router->post('/admin/settings/api/test-connection', [ApiSettingsController::class, 'testConnection'], $authMiddleware)->name('admin.settings.api.test_connection');
$router->get('/admin/settings/api/generate-key', [ApiSettingsController::class, 'generateKey'], $authMiddleware)->name('admin.settings.api.generate_key');

// Mail Settings
$router->get('/admin/settings/mail', [MailSettingsController::class, 'index'], $authMiddleware)->name('admin.settings.mail');
$router->post('/admin/settings/mail/update', [MailSettingsController::class, 'update'], $authMiddleware)->name('admin.settings.mail.update');
$router->post('/admin/settings/mail/test', [MailSettingsController::class, 'sendTest'], $authMiddleware)->name('admin.settings.mail.test');

// Language Management
$router->get('/admin/settings/languages', [LanguageController::class, 'index'], $authMiddleware)->name('admin.settings.languages');
$router->post('/admin/settings/languages/activate', [LanguageController::class, 'activate'], $authMiddleware)->name('admin.settings.languages.activate');
$router->post('/admin/settings/languages/deactivate', [LanguageController::class, 'deactivate'], $authMiddleware)->name('admin.settings.languages.deactivate');
$router->post('/admin/settings/languages/set-default', [LanguageController::class, 'setDefault'], $authMiddleware)->name('admin.settings.languages.set_default');
$router->post('/admin/settings/languages/set-fallback', [LanguageController::class, 'setFallback'], $authMiddleware)->name('admin.settings.languages.set_fallback');
$router->post('/admin/settings/languages/create-file', [LanguageController::class, 'createFile'], $authMiddleware)->name('admin.settings.languages.create_file');

// Translation Management
$router->get('/admin/settings/translations', [TranslationController::class, 'index'], $authMiddleware)->name('admin.settings.translations');
$router->get('/admin/settings/translations/create', [TranslationController::class, 'create'], $authMiddleware)->name('admin.settings.translations.create');
$router->post('/admin/settings/translations/store', [TranslationController::class, 'store'], $authMiddleware)->name('admin.settings.translations.store');
$router->get('/admin/settings/translations/{id}/edit', [TranslationController::class, 'edit'], $authMiddleware)->name('admin.settings.translations.edit');
$router->post('/admin/settings/translations/{id}/update', [TranslationController::class, 'update'], $authMiddleware)->name('admin.settings.translations.update');
$router->post('/admin/settings/translations/{id}/delete', [TranslationController::class, 'delete'], $authMiddleware)->name('admin.settings.translations.delete');
$router->post('/admin/settings/translations/import', [TranslationController::class, 'import'], $authMiddleware)->name('admin.settings.translations.import');
$router->get('/admin/settings/translations/export', [TranslationController::class, 'export'], $authMiddleware)->name('admin.settings.translations.export');
$router->get('/admin/settings/translations/history', [TranslationController::class, 'history'], $authMiddleware)->name('admin.settings.translations.history');

// Webhook Settings
$router->get('/admin/settings/webhooks', [WebhookController::class, 'index'], $authMiddleware)->name('admin.settings.webhooks');
$router->get('/admin/settings/webhooks/create', [WebhookController::class, 'create'], $authMiddleware)->name('admin.settings.webhooks.create');
$router->post('/admin/settings/webhooks/store', [WebhookController::class, 'store'], $authMiddleware)->name('admin.settings.webhooks.store');
$router->get('/admin/settings/webhooks/{id}/edit', [WebhookController::class, 'edit'], $authMiddleware)->name('admin.settings.webhooks.edit');
$router->post('/admin/settings/webhooks/{id}/update', [WebhookController::class, 'update'], $authMiddleware)->name('admin.settings.webhooks.update');
$router->post('/admin/settings/webhooks/{id}/delete', [WebhookController::class, 'delete'], $authMiddleware)->name('admin.settings.webhooks.delete');
$router->post('/admin/settings/webhooks/{id}/test', [WebhookController::class, 'test'], $authMiddleware)->name('admin.settings.webhooks.test');
$router->get('/admin/settings/webhooks/{id}/logs', [WebhookController::class, 'logs'], $authMiddleware)->name('admin.settings.webhooks.logs');

// Backup & Export
$router->get('/admin/settings/backup', [SettingsController::class, 'backup'], $authMiddleware)->name('admin.settings.backup');
$router->post('/admin/settings/import', [SettingsController::class, 'import'], $authMiddleware)->name('admin.settings.import');

// SMS Settings
$router->get('/admin/settings/sms', [SmsSettingsController::class, 'index'], $authMiddleware)->name('admin.settings.sms');
$router->get('/admin/settings/sms/gateways/create', [SmsSettingsController::class, 'createGateway'], $authMiddleware)->name('admin.settings.sms.gateways.create');
$router->post('/admin/settings/sms/gateways/store', [SmsSettingsController::class, 'storeGateway'], $authMiddleware)->name('admin.settings.sms.gateways.store');
$router->get('/admin/settings/sms/gateways/{id}/edit', [SmsSettingsController::class, 'editGateway'], $authMiddleware)->name('admin.settings.sms.gateways.edit');
$router->post('/admin/settings/sms/gateways/{id}/update', [SmsSettingsController::class, 'updateGateway'], $authMiddleware)->name('admin.settings.sms.gateways.update');
$router->post('/admin/settings/sms/gateways/{id}/delete', [SmsSettingsController::class, 'deleteGateway'], $authMiddleware)->name('admin.settings.sms.gateways.delete');
$router->get('/admin/settings/sms/gateways/{id}/test', [SmsSettingsController::class, 'testGateway'], $authMiddleware)->name('admin.settings.sms.gateways.test');
$router->post('/admin/settings/sms/gateways/{id}/switch-mode', [SmsSettingsController::class, 'switchMode'], $authMiddleware)->name('admin.settings.sms.gateways.switch_mode');
$router->get('/admin/settings/sms/gateways/{id}/set-default', [SmsSettingsController::class, 'setDefault'], $authMiddleware)->name('admin.settings.sms.gateways.set_default');
$router->get('/admin/settings/sms/gateways/{id}/toggle', [SmsSettingsController::class, 'toggleStatus'], $authMiddleware)->name('admin.settings.sms.gateways.toggle');

// Wallet Settings
$router->get('/admin/settings/wallet', [WalletSettingsController::class, 'index'], $authMiddleware)->name('admin.settings.wallet');
$router->post('/admin/settings/wallet/update', [WalletSettingsController::class, 'update'], $authMiddleware)->name('admin.settings.wallet.update');
$router->get('/admin/settings/wallet/gateways/create', [WalletSettingsController::class, 'createGateway'], $authMiddleware)->name('admin.settings.wallet.gateways.create');
$router->post('/admin/settings/wallet/gateways/store', [WalletSettingsController::class, 'storeGateway'], $authMiddleware)->name('admin.settings.wallet.gateways.store');
$router->get('/admin/settings/wallet/gateways/{id}/edit', [WalletSettingsController::class, 'editGateway'], $authMiddleware)->name('admin.settings.wallet.gateways.edit');
$router->post('/admin/settings/wallet/gateways/{id}/update', [WalletSettingsController::class, 'updateGateway'], $authMiddleware)->name('admin.settings.wallet.gateways.update');
$router->post('/admin/settings/wallet/gateways/{id}/delete', [WalletSettingsController::class, 'deleteGateway'], $authMiddleware)->name('admin.settings.wallet.gateways.delete');
$router->get('/admin/settings/wallet/gateways/{id}/test', [WalletSettingsController::class, 'testGateway'], $authMiddleware)->name('admin.settings.wallet.gateways.test');
$router->get('/admin/settings/wallet/gateways/{id}/set-default', [WalletSettingsController::class, 'setDefault'], $authMiddleware)->name('admin.settings.wallet.gateways.set_default');
$router->get('/admin/settings/wallet/gateways/{id}/toggle', [WalletSettingsController::class, 'toggleStatus'], $authMiddleware)->name('admin.settings.wallet.gateways.toggle');

// Maintenance Mode
$router->get('/admin/maintenance', [MaintenanceController::class, 'index'], $authMiddleware)->name('admin.maintenance');
$router->post('/admin/maintenance/update', [MaintenanceController::class, 'update'], $authMiddleware)->name('admin.maintenance.update');
$router->post('/admin/maintenance/toggle', [MaintenanceController::class, 'toggle'], $authMiddleware)->name('admin.maintenance.toggle');
$router->get('/admin/maintenance/remove-background', [MaintenanceController::class, 'removeBackgroundImage'], $authMiddleware)->name('admin.maintenance.remove_background');

// Health Check / System Monitoring (Admin only)
$router->get('/admin/health', [HealthCheckController::class, 'index'], $authMiddleware)->name('admin.health');

// Log Viewer (Admin only)
$router->get('/admin/logs/cron', [LogsController::class, 'cronLogs'], $authMiddleware)->name('admin.logs.cron');
$router->get('/admin/logs/health', [LogsController::class, 'healthLogs'], $authMiddleware)->name('admin.logs.health');
$router->get('/admin/logs/sms-queue', [LogsController::class, 'smsQueueLogs'], $authMiddleware)->name('admin.logs.sms_queue');
$router->post('/admin/logs/clear', [LogsController::class, 'clearLog'], $authMiddleware)->name('admin.logs.clear');
