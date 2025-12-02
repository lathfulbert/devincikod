<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();
$db = \App\Core\Database\Database::getInstance();

echo "=== Modules dans la BD ===\n";

try {
    $modules = $db->query('SELECT name, is_active, is_installed FROM modules ORDER BY name')->fetchAll();

    foreach ($modules as $m) {
        $status = $m['is_active'] ? '✓' : '✗';
        $installed = $m['is_installed'] ? '✓' : '✗';
        echo sprintf("%-20s Active: %s  Installed: %s\n", $m['name'], $status, $installed);
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Recherche du module Users ===\n";
$users = $db->query("SELECT * FROM modules WHERE name = 'Users'")->fetch();

if ($users) {
    echo "Module Users trouvé dans la BD:\n";
    print_r($users);

    if (!$users['is_active']) {
        echo "\n⚠️ Le module Users est désactivé! Activation...\n";
        $db->query("UPDATE modules SET is_active = 1 WHERE name = 'Users'");
        echo "✓ Module Users activé!\n";
    } else {
        echo "\n✓ Module Users est ACTIF\n";
    }
} else {
    echo "❌ Module Users non trouvé dans la BD!\n";
    echo "\nAjout du module Users...\n";

    // Insérer le module Users s'il n'existe pas
    $db->query(
        "INSERT INTO modules (name, slug, version, description, author, is_active, is_installed, config, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
        [
            'Users',
            'users',
            '1.0.0',
            'User Management Module',
            'SunuFramework',
            1, // Active
            1, // Installed
            json_encode([])
        ]
    );

    echo "✓ Module Users ajouté et activé!\n";
}

echo "\n=== Vérification finale ===\n";
echo "Rafraîchissez votre page pour voir le menu Users!\n";
