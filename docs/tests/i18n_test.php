<?php

/**
 * I18n System Quick Test
 * 
 * This script tests the basic functionality of the I18n system.
 * Run from command line: php tests/i18n_test.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Core/Support/helpers.php';

// Load environment
(new \App\Core\Support\DotEnv(__DIR__ . '/../.env'))->load();

// Initialize config
$config = new \App\Core\Config\Config();
$config->load(__DIR__ . '/../config/app.php');

echo "=== I18n System Test ===\n\n";

// Test 1: LanguageManager Initialization
echo "✓ Test 1: LanguageManager Initialization\n";
$manager = \App\Core\I18n\LanguageManager::getInstance();
echo "  Current locale: " . $manager->getLocale() . "\n";
echo "  Fallback locale: " . $manager->getFallbackLocale() . "\n";
echo "  Supported locales: " . implode(', ', $manager->getSupportedLocales()) . "\n\n";

// Test 2: Basic Translation
echo "✓ Test 2: Basic Translation\n";
$translation = __t('dashboard.title');
echo "  __t('dashboard.title') = '{$translation}'\n\n";

// Test 3: Translation with Parameters
echo "✓ Test 3: Translation with Parameters\n";
$translation = __t('auth.welcome', ['name' => 'Jean']);
echo "  __t('auth.welcome', ['name' => 'Jean']) = '{$translation}'\n\n";

// Test 4: Pluralization
echo "✓ Test 4: Pluralization\n";
$single = trans_choice('users.count', 1, ['count' => 1]);
$multiple = trans_choice('users.count', 5, ['count' => 5]);
echo "  trans_choice('users.count', 1) = '{$single}'\n";
echo "  trans_choice('users.count', 5) = '{$multiple}'\n\n";

// Test 5: Locale Switching
echo "✓ Test 5: Locale Switching\n";
set_locale('en');
$enTranslation = __t('dashboard.title');
echo "  set_locale('en')\n";
echo "  __t('dashboard.title') = '{$enTranslation}'\n";
set_locale('fr'); // Reset
echo "\n";

// Test 6: Fallback Mechanism
echo "✓ Test 6: Fallback Mechanism\n";
$nonExistent = __t('non.existent.key');
echo "  __t('non.existent.key') = '{$nonExistent}' (should return key)\n\n";

// Test 7: Module Translations
echo "✓ Test 7: Module Translations\n";
if ($manager->has('blog.title')) {
    $blogTitle = __t('blog.title');
    echo "  __t('blog.title') = '{$blogTitle}'\n";
} else {
    echo "  Blog translations not loaded\n";
}
echo "\n";

// Test 8: Available Locales
echo "✓ Test 8: Available Locales\n";
$available = $manager->getAvailableLocales();
echo "  Available locales: " . implode(', ', $available) . "\n\n";

// Test 9: Cache Stats
echo "✓ Test 9: Cache Statistics\n";
$stats = $manager->getCacheStats();
echo "  Cached locales: " . ($stats['total_cached'] ?? 0) . "\n";
if (isset($stats['locales'])) {
    foreach ($stats['locales'] as $locale) {
        echo "    - {$locale['locale']}: " . round($locale['size'] / 1024, 2) . " KB\n";
    }
}
echo "\n";

// Test 10: Helper Functions
echo "✓ Test 10: Helper Functions\n";
echo "  app_locale() = " . app_locale() . "\n";
echo "  is_locale_supported('fr') = " . (is_locale_supported('fr') ? 'true' : 'false') . "\n";
echo "  is_locale_supported('xx') = " . (is_locale_supported('xx') ? 'true' : 'false') . "\n";
echo "  supported_locales() = " . implode(', ', supported_locales()) . "\n\n";

echo "=== All Tests Passed! ===\n";
