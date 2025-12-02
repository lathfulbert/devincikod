<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Vérification des permissions API Keys ===\n\n";

// Simuler une session (remplacez par votre user_id réel)
$_SESSION['user_id'] = 1; // Changez ceci avec votre ID utilisateur

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo "✗ Aucun utilisateur en session\n";
    exit(1);
}

$db = \App\Core\Database\Database::getInstance();

// Vérifier l'utilisateur
$user = $db->query("SELECT id, username FROM users WHERE id = ?", [$userId])->fetch();

if ($user) {
    echo "✓ Utilisateur connecté : {$user['username']} (ID: {$user['id']})\n\n";
} else {
    echo "✗ Utilisateur non trouvé\n";
    exit(1);
}

// Vérifier les permissions
echo "=== Vérification des permissions requises ===\n\n";

$requiredPermissions = [
    'apikeys.view' => 'Voir les clés API',
    'apikeys.manage' => 'Générer les clés API',
    'apikeys.revoke' => 'Révoquer les clés API',
];

// Vérifier si la table permissions existe
try {
    $permissions = $db->query(
        "SELECT p.name
         FROM permissions p
         INNER JOIN role_permission rp ON p.id = rp.permission_id
         INNER JOIN user_role ur ON rp.role_id = ur.role_id
         WHERE ur.user_id = ?",
        [$userId]
    )->fetchAll();

    echo "Permissions de l'utilisateur :\n";
    if (empty($permissions)) {
        echo "  ⚠️  AUCUNE PERMISSION TROUVÉE\n\n";
        echo "  C'est probablement la cause du problème !\n";
        echo "  Les middlewares bloquent les requêtes.\n\n";
    } else {
        foreach ($permissions as $perm) {
            echo "  - {$perm['name']}\n";
        }
        echo "\n";
    }

    foreach ($requiredPermissions as $perm => $desc) {
        $found = false;
        foreach ($permissions as $p) {
            if ($p['name'] === $perm) {
                $found = true;
                break;
            }
        }

        if ($found) {
            echo "✓ Permission '$perm' : OK\n";
        } else {
            echo "✗ Permission '$perm' : MANQUANTE\n";
        }
    }

} catch (Exception $e) {
    echo "⚠️  Erreur : " . $e->getMessage() . "\n";
    echo "La table permissions n'existe peut-être pas\n";
}

echo "\n=== Solution ===\n";
echo "Si les permissions sont manquantes, vous avez 2 options :\n\n";
echo "1. Retirer temporairement les middlewares de permissions\n";
echo "2. Ajouter les permissions nécessaires à votre utilisateur\n";
