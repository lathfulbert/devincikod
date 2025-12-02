<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Test du Système de Traduction (I18n) ===\n\n";

// 1. Configuration
echo "1. Configuration des langues:\n";
$config = config('app');
echo "   - Locale par défaut: " . ($config['locale'] ?? 'NON DÉFINI') . "\n";
echo "   - Locale de fallback: " . ($config['fallback_locale'] ?? 'NON DÉFINI') . "\n";
echo "   - Langues supportées: " . implode(', ', $config['supported_locales'] ?? []) . "\n";

// 2. LanguageManager
echo "\n2. LanguageManager:\n";
try {
    $langManager = \App\Core\I18n\LanguageManager::getInstance();
    echo "   ✓ LanguageManager initialisé\n";

    $currentLocale = app_locale();
    echo "   - Locale actuelle: $currentLocale\n";

    $supportedLocales = supported_locales();
    echo "   - Langues supportées: " . implode(', ', $supportedLocales) . "\n";
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n";
}

// 3. Configuration des langues
echo "\n3. Métadonnées des langues (config/languages.php):\n";
$languages = config('languages');
if ($languages) {
    echo "   ✓ " . count($languages) . " langues configurées:\n";
    foreach (array_slice($languages, 0, 5) as $code => $lang) {
        echo "     - $code: {$lang['name']} ({$lang['native_name']}) - {$lang['flag']}\n";
    }
    echo "     ... et " . (count($languages) - 5) . " autres\n";
} else {
    echo "   ✗ Aucune langue configurée\n";
}

// 4. Répertoires de traductions
echo "\n4. Répertoires de traductions:\n";
$possiblePaths = [
    __DIR__ . '/lang',
    __DIR__ . '/resources/lang',
    __DIR__ . '/storage/lang',
];

$foundPath = null;
foreach ($possiblePaths as $path) {
    if (is_dir($path)) {
        $foundPath = $path;
        echo "   ✓ Trouvé: $path\n";

        // Lister les langues disponibles
        $dirs = array_filter(scandir($path), function($item) use ($path) {
            return $item !== '.' && $item !== '..' && is_dir($path . '/' . $item);
        });

        if (!empty($dirs)) {
            echo "     Langues avec traductions: " . implode(', ', $dirs) . "\n";
        } else {
            echo "     ⚠️  Aucune traduction trouvée\n";
        }
        break;
    }
}

if (!$foundPath) {
    echo "   ⚠️  Aucun répertoire de traductions trouvé\n";
    echo "   Recherche dans: \n";
    foreach ($possiblePaths as $path) {
        echo "     - $path (non trouvé)\n";
    }
}

// 5. Test des fonctions de traduction
echo "\n5. Test des fonctions de traduction:\n";
echo "   - function trans() existe: " . (function_exists('trans') ? "OUI" : "NON") . "\n";
echo "   - function trans_choice() existe: " . (function_exists('trans_choice') ? "OUI" : "NON") . "\n";
echo "   - function app_locale() existe: " . (function_exists('app_locale') ? "OUI" : "NON") . "\n";
echo "   - function set_locale() existe: " . (function_exists('set_locale') ? "OUI" : "NON") . "\n";
echo "   - function supported_locales() existe: " . (function_exists('supported_locales') ? "OUI" : "NON") . "\n";

// 6. Test de traduction
echo "\n6. Test de traduction simple:\n";
try {
    $testKey = 'common.welcome';
    $translation = trans($testKey);
    echo "   trans('$testKey') = '$translation'\n";

    if ($translation === $testKey) {
        echo "   ⚠️  Clé non traduite (retourne la clé elle-même)\n";
    } else {
        echo "   ✓ Traduction trouvée\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n";
}

// 7. Middleware SetLocale
echo "\n7. Middleware SetLocaleMiddleware:\n";
$middlewareClass = 'App\\Core\\I18n\\Middleware\\SetLocaleMiddleware';
if (class_exists($middlewareClass)) {
    echo "   ✓ Middleware existe\n";
} else {
    echo "   ✗ Middleware non trouvé\n";
}

// 8. Module I18n
echo "\n8. Module I18n:\n";
$i18nModulePath = __DIR__ . '/Modules/I18n';
if (is_dir($i18nModulePath)) {
    echo "   ✓ Module I18n existe: $i18nModulePath\n";

    // Vérifier le contrôleur
    if (file_exists($i18nModulePath . '/Controllers/I18nController.php')) {
        echo "   ✓ I18nController existe\n";
    }

    // Vérifier les vues
    if (is_dir($i18nModulePath . '/Views')) {
        echo "   ✓ Vues I18n existent\n";
    }
} else {
    echo "   ⚠️  Module I18n non trouvé\n";
}

// 9. Cache des traductions
echo "\n9. Cache des traductions:\n";
$cachePath = __DIR__ . '/storage/cache/i18n';
if (is_dir($cachePath)) {
    echo "   ✓ Répertoire de cache existe: $cachePath\n";
    $cacheFiles = glob($cachePath . '/*.php');
    echo "   - " . count($cacheFiles) . " fichier(s) en cache\n";
} else {
    echo "   ⚠️  Répertoire de cache non trouvé\n";
}

// 10. Language Selector Component
echo "\n10. Composant Language Selector:\n";
$selectorPath = __DIR__ . '/resources/views/backend/components/language-selector.php';
if (file_exists($selectorPath)) {
    echo "   ✓ Composant language-selector existe\n";
} else {
    echo "   ✗ Composant non trouvé\n";
}

echo "\n=== Résumé ===\n";
echo "Configuration: " . (isset($config['locale']) ? "✓" : "✗") . "\n";
echo "LanguageManager: " . (isset($langManager) ? "✓" : "✗") . "\n";
echo "Fonctions helpers: ✓\n";
echo "Langues configurées: " . (count($languages ?? []) > 0 ? "✓ (" . count($languages) . ")" : "✗") . "\n";
echo "Traductions: " . ($foundPath ? "✓" : "⚠️  À vérifier") . "\n";
