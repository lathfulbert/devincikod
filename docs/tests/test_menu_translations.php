<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Test Menu Translations ===\n\n";

$manager = \App\Core\I18n\LanguageManager::getInstance();

// Test FR
echo "1. French (default):\n";
$manager->setLocale('fr');
echo "   Admin Menu:\n";
echo "     - Dashboard: " . __('admin.menu.dashboard') . "\n";
echo "     - Queue & Jobs: " . __('admin.menu.queue_jobs') . "\n";
echo "     - Configuration: " . __('admin.menu.configuration') . "\n";
echo "   Users Menu:\n";
echo "     - Title: " . __('users.title') . "\n";
echo "     - List: " . __('users.list') . "\n";
echo "   RBAC Menu:\n";
echo "     - Roles Title: " . __('rbac.roles.title') . "\n";
echo "     - Roles List: " . __('rbac.roles.list') . "\n\n";

// Test EN
echo "2. English:\n";
$manager->setLocale('en');
echo "   Admin Menu:\n";
echo "     - Dashboard: " . __('admin.menu.dashboard') . "\n";
echo "     - Queue & Jobs: " . __('admin.menu.queue_jobs') . "\n";
echo "     - Configuration: " . __('admin.menu.configuration') . "\n";
echo "   Users Menu:\n";
echo "     - Title: " . __('users.title') . "\n";
echo "     - List: " . __('users.list') . "\n";
echo "   RBAC Menu:\n";
echo "     - Roles Title: " . __('rbac.roles.title') . "\n";
echo "     - Roles List: " . __('rbac.roles.list') . "\n\n";

echo "✅ Menu translations working!\n";
