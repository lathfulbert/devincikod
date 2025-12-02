<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

$_SESSION['user_id'] = 1;

echo "=== Debug du modèle User ===\n\n";

$userId = $_SESSION['user_id'];

// Test 1: Requête directe SQL
echo "1. Requête SQL Directe:\n";
$db = \App\Core\Database\Database::getInstance();
$userDirect = $db->query("SELECT id, username, api_key, api_key_created_at FROM users WHERE id = ?", [$userId])->fetch();

if ($userDirect) {
    echo "   ✓ Utilisateur: {$userDirect['username']}\n";
    echo "   - api_key: " . ($userDirect['api_key'] ? substr($userDirect['api_key'], 0, 20) . "..." : "NULL") . "\n";
    echo "   - api_key_created_at: " . ($userDirect['api_key_created_at'] ?? "NULL") . "\n";
    echo "   - hasApiKey (direct): " . (!empty($userDirect['api_key']) ? "TRUE" : "FALSE") . "\n";
}

// Test 2: Via le modèle User
echo "\n2. Via le modèle User::find():\n";
$user = \Modules\Users\Models\User::find($userId);

if ($user) {
    echo "   ✓ Utilisateur: {$user->username}\n";
    echo "   - api_key: " . ($user->api_key ? substr($user->api_key, 0, 20) . "..." : "NULL") . "\n";
    echo "   - api_key_created_at: " . ($user->api_key_created_at ?? "NULL") . "\n";
    echo "   - hasApiKey (model): " . (!empty($user->api_key) ? "TRUE" : "FALSE") . "\n";
}

// Test 3: Vérifier les attributs disponibles
echo "\n3. Attributs du modèle User:\n";
if ($user) {
    $attributes = get_object_vars($user);
    foreach ($attributes as $key => $value) {
        if (strpos($key, 'api') !== false) {
            echo "   - $key: " . (is_string($value) && strlen($value) > 20 ? substr($value, 0, 20) . "..." : $value) . "\n";
        }
    }
}

// Comparaison
echo "\n=== Comparaison ===\n";
echo "SQL Direct - api_key: " . ($userDirect['api_key'] ? "EXISTS" : "NULL") . "\n";
echo "User Model - api_key: " . ($user->api_key ?? "NULL") . "\n";

if (!empty($userDirect['api_key']) && empty($user->api_key)) {
    echo "\n⚠️  PROBLÈME DÉTECTÉ:\n";
    echo "La clé existe en base de données mais le modèle User ne la récupère pas!\n";
    echo "Solution: Le modèle User doit rafraîchir ses données ou ne charge pas la colonne api_key.\n";
} elseif (!empty($userDirect['api_key']) && !empty($user->api_key)) {
    echo "\n✓ OK: Les deux méthodes récupèrent la clé correctement\n";
}
