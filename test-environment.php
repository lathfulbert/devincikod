<?php
/**
 * Script de Test des Environnements
 *
 * Ce script permet de tester rapidement la configuration des environnements
 * Usage: php test-environment.php ou visitez /test-environment.php dans le navigateur
 */

// Load environment
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Load helpers
require_once __DIR__ . '/Core/Support/helpers.php';

// Determine if running in CLI or web
$isCli = php_sapi_name() === 'cli';

// Function to output (works for both CLI and web)
function output($message, $type = 'info') {
    global $isCli;

    $colors = [
        'success' => $isCli ? "\033[32m" : '<span style="color: green;">',
        'info' => $isCli ? "\033[36m" : '<span style="color: blue;">',
        'warning' => $isCli ? "\033[33m" : '<span style="color: orange;">',
        'error' => $isCli ? "\033[31m" : '<span style="color: red;">',
        'reset' => $isCli ? "\033[0m" : '</span>',
    ];

    if ($isCli) {
        echo $colors[$type] . $message . $colors['reset'] . "\n";
    } else {
        echo $colors[$type] . htmlspecialchars($message) . $colors['reset'] . "<br>\n";
    }
}

function section($title) {
    global $isCli;
    if ($isCli) {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo " $title\n";
        echo str_repeat('=', 60) . "\n";
    } else {
        echo "<h2 style='border-bottom: 2px solid #333; padding-bottom: 10px;'>$title</h2>";
    }
}

// Start output
if (!$isCli) {
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Test Environnement</title>";
    echo "<style>body { font-family: monospace; padding: 20px; background: #f5f5f5; }</style>";
    echo "</head><body>";
    echo "<h1>🔧 Test de Configuration des Environnements</h1>";
}

section("1. INFORMATIONS D'ENVIRONNEMENT");

output("Environnement actuel : " . environment(), 'info');
output("Mode debug : " . (isDebugMode() ? 'ACTIVÉ' : 'DÉSACTIVÉ'), isDebugMode() ? 'warning' : 'success');

section("2. TESTS DES HELPERS");

$tests = [
    'isDevelopment()' => isDevelopment(),
    'isProduction()' => isProduction(),
    'isStaging()' => isStaging(),
    'isTesting()' => isTesting(),
    'isDebugMode()' => isDebugMode(),
];

foreach ($tests as $function => $result) {
    $status = $result ? '✓ TRUE' : '✗ FALSE';
    $type = $result ? 'success' : 'info';
    output("  $function -> $status", $type);
}

section("3. VARIABLES D'ENVIRONNEMENT CHARGÉES");

$envVars = [
    'APP_NAME' => env('APP_NAME'),
    'APP_ENV' => env('APP_ENV'),
    'APP_URL' => env('APP_URL'),
    'APP_DEBUG' => env('APP_DEBUG'),
    'DB_CONNECTION' => env('DB_CONNECTION'),
    'DB_HOST' => env('DB_HOST'),
    'DB_DATABASE' => env('DB_DATABASE'),
    'DB_USERNAME' => env('DB_USERNAME'),
    'DB_PASSWORD' => env('DB_PASSWORD') ? '***MASQUÉ***' : '(vide)',
];

foreach ($envVars as $key => $value) {
    $displayValue = $value ?? '(non défini)';
    output("  $key = $displayValue", 'info');
}

section("4. CONFIGURATION APP");

if (file_exists(__DIR__ . '/config/app.php')) {
    $appConfig = require __DIR__ . '/config/app.php';

    $configItems = [
        'env' => $appConfig['env'] ?? 'N/A',
        'debug' => $appConfig['debug'] ?? false,
        'show_error_details' => $appConfig['show_error_details'] ?? 'N/A',
        'query_log_enabled' => $appConfig['query_log_enabled'] ?? 'N/A',
        'force_https' => $appConfig['force_https'] ?? 'N/A',
        'maintenance' => $appConfig['maintenance'] ?? 'N/A',
    ];

    foreach ($configItems as $key => $value) {
        $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        output("  $key = $displayValue", 'info');
    }
} else {
    output("  Fichier config/app.php non trouvé", 'error');
}

section("5. RECOMMANDATIONS");

$env = environment();
$recommendations = [];

// Check for development
if (isDevelopment()) {
    output("✓ Configuration développement détectée", 'success');

    if (!isDebugMode()) {
        $recommendations[] = "⚠ APP_DEBUG devrait être 'true' en développement";
    }

    if (env('DB_DATABASE') && !str_contains(env('DB_DATABASE'), 'dev')) {
        $recommendations[] = "⚠ Envisagez d'utiliser une base de données dédiée (ex: sunuframework2_dev)";
    }
}

// Check for production
if (isProduction()) {
    output("✓ Configuration production détectée", 'success');

    if (isDebugMode()) {
        $recommendations[] = "⚠ CRITIQUE: APP_DEBUG devrait être 'false' en production!";
    }

    if (!env('DB_PASSWORD')) {
        $recommendations[] = "⚠ CRITIQUE: DB_PASSWORD ne devrait pas être vide en production!";
    }

    if (!env('APP_KEY')) {
        $recommendations[] = "⚠ APP_KEY devrait être définie en production";
    }
}

// Check for staging
if (isStaging()) {
    output("✓ Configuration staging détectée", 'success');

    if (env('DB_DATABASE') && !str_contains(env('DB_DATABASE'), 'staging')) {
        $recommendations[] = "⚠ Envisagez d'utiliser une base de données staging séparée";
    }
}

if (empty($recommendations)) {
    output("✓ Aucun problème détecté", 'success');
} else {
    output("\nRecommandations:", 'warning');
    foreach ($recommendations as $rec) {
        output("  $rec", 'warning');
    }
}

section("6. FICHIERS D'ENVIRONNEMENT DISPONIBLES");

$envFiles = [
    '.env' => 'Fichier principal',
    '.env.example' => 'Template général',
    '.env.development' => 'Configuration développement',
    '.env.staging' => 'Configuration staging',
    '.env.production' => 'Configuration production',
];

foreach ($envFiles as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? '✓ Présent' : '✗ Absent';
    $type = $exists ? 'success' : 'error';
    output("  $file ($description) -> $status", $type);
}

section("7. RÉSUMÉ");

output("Environnement: " . environment(), 'info');
output("État: " . (empty($recommendations) ? 'Bon' : 'Nécessite attention'),
    empty($recommendations) ? 'success' : 'warning');
output("Date du test: " . date('Y-m-d H:i:s'), 'info');

// End output
if (!$isCli) {
    echo "<hr>";
    echo "<p><em>Pour plus d'informations, consultez ENVIRONMENT_SETUP.md</em></p>";
    echo "</body></html>";
} else {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "Test terminé!\n\n";
    echo "Pour plus d'informations, consultez ENVIRONMENT_SETUP.md\n";
}
