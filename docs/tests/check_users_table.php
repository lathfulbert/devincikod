<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

$db = \App\Core\Database\Database::getInstance();

echo "=== Vérification de la table users ===\n\n";

try {
    // Vérifier la structure de la table users
    $columns = $db->query("DESCRIBE users")->fetchAll();

    echo "Colonnes de la table users :\n";
    $hasApiKey = false;
    $hasApiKeyCreatedAt = false;

    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";

        if ($col['Field'] === 'api_key') {
            $hasApiKey = true;
        }
        if ($col['Field'] === 'api_key_created_at') {
            $hasApiKeyCreatedAt = true;
        }
    }

    echo "\n=== Vérification des colonnes API ===\n";

    if ($hasApiKey) {
        echo "✓ Colonne 'api_key' existe\n";
    } else {
        echo "✗ Colonne 'api_key' MANQUANTE\n";
        echo "\nAjout de la colonne api_key...\n";
        $db->query("ALTER TABLE users ADD COLUMN api_key VARCHAR(255) NULL DEFAULT NULL");
        echo "✓ Colonne 'api_key' ajoutée\n";
    }

    if ($hasApiKeyCreatedAt) {
        echo "✓ Colonne 'api_key_created_at' existe\n";
    } else {
        echo "✗ Colonne 'api_key_created_at' MANQUANTE\n";
        echo "\nAjout de la colonne api_key_created_at...\n";
        $db->query("ALTER TABLE users ADD COLUMN api_key_created_at DATETIME NULL DEFAULT NULL");
        echo "✓ Colonne 'api_key_created_at' ajoutée\n";
    }

    // Vérifier l'utilisateur connecté
    echo "\n=== Vérification de l'utilisateur connecté ===\n";
    if (isset($_SESSION['user_id'])) {
        echo "User ID en session : {$_SESSION['user_id']}\n";

        $user = $db->query("SELECT id, username, api_key FROM users WHERE id = ?", [$_SESSION['user_id']])->fetch();

        if ($user) {
            echo "✓ Utilisateur trouvé : {$user['username']}\n";
            echo "  - API Key : " . ($user['api_key'] ? substr($user['api_key'], 0, 16) . '...' : 'Aucune') . "\n";
        } else {
            echo "✗ Utilisateur non trouvé en BD\n";
        }
    } else {
        echo "✗ Aucun utilisateur connecté en session\n";
        echo "Note : Vous devez être connecté pour voir les clés API\n";
    }

} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}
