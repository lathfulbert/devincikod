<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Vérification des vues Settings ===\n\n";

// Vues attendues d'après le menu
$expectedViews = [
    'settings/index' => 'Vue d\'ensemble',
    'settings/site' => 'Paramètres du site',
    'settings/theme' => 'Thème & Apparence',
    'settings/api' => 'API & Services',
    'settings/mail' => 'Configuration Mail',
    'settings/sms' => 'Configuration SMS',
    'settings/wallet' => 'Configuration Wallet',
    'settings/languages' => 'Langues',
    'settings/translations' => 'Traductions',
    'settings/webhooks' => 'Webhooks',
];

echo "1. Test de résolution des vues Settings\n";
$view = new \App\Core\View\View(__DIR__ . '/resources/views');
\App\Core\View\View::clearCache();

$reflection = new ReflectionClass($view);
$method = $reflection->getMethod('resolveViewPath');
$method->setAccessible(true);

foreach ($expectedViews as $viewPath => $name) {
    $resolved = $method->invoke($view, $viewPath);

    if ($resolved && file_exists($resolved)) {
        echo "   ✓ $name\n";
    } else {
        echo "   ✗ $name NON RÉSOLUE\n";
    }
}

echo "\n2. Recherche des vues dans le backup\n";
$backupPath = __DIR__ . '/templates_backup_20251201/backend/settings/';
if (is_dir($backupPath)) {
    echo "   Dossier backup trouvé : $backupPath\n";
    $files = glob($backupPath . '*.php');
    echo "   Fichiers disponibles :\n";
    foreach ($files as $file) {
        echo "     - " . basename($file) . "\n";
    }
} else {
    echo "   ✗ Dossier backup non trouvé\n";
}

echo "\n3. Vérification du module Settings\n";
$settingsModulePath = __DIR__ . '/Modules/Settings/Views/';
if (is_dir($settingsModulePath)) {
    echo "   Module Settings trouvé : $settingsModulePath\n";
    $files = glob($settingsModulePath . '*.php');
    echo "   Fichiers disponibles :\n";
    foreach ($files as $file) {
        echo "     - " . basename($file) . "\n";
    }
} else {
    echo "   ✗ Module Settings Views non trouvé\n";
}
