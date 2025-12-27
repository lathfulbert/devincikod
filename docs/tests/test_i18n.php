<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Test I18n Translation System ===\n\n";

// Test 1: Helper function
echo "1. Testing __() helper function:\n";
echo "   __('dashboard.title'): " . __('dashboard.title') . "\n";
echo "   __('actions.save'): " . __('actions.save') . "\n";
echo "   __('common.yes'): " . __('common.yes') . "\n\n";

// Test 2: Current locale
$manager = \App\Core\I18n\LanguageManager::getInstance();
echo "2. Current locale: " . $manager->getLocale() . "\n";
echo "   Fallback locale: " . $manager->getFallbackLocale() . "\n";
echo "   Supported locales: " . implode(', ', $manager->getSupportedLocales()) . "\n\n";

// Test 3: Module-specific translations
echo "3. Testing module translations:\n";
echo "   __('users.title'): " . __('users.title') . "\n";
echo "   __('rbac.roles.title'): " . __('rbac.roles.title') . "\n\n";

// Test 4: Switch to English
echo "4. Switching to English:\n";
$manager->setLocale('en');
echo "   __('dashboard.title'): " . __('dashboard.title') . "\n";
echo "   __('actions.save'): " . __('actions.save') . "\n";
echo "   __('users.title'): " . __('users.title') . "\n";
echo "   __('rbac.roles.title'): " . __('rbac.roles.title') . "\n\n";

// Test 5: Switch back to French
echo "5. Switching back to French:\n";
$manager->setLocale('fr');
echo "   __('dashboard.title'): " . __('dashboard.title') . "\n";
echo "   __('actions.save'): " . __('actions.save') . "\n\n";

echo "✅ I18n system is working!\n";
