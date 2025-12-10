<?php
/**
 * Script de test pour déboguer l'authentification API
 */

require __DIR__ . '/vendor/autoload.php';

// Charger l'application
$app = require __DIR__ . '/bootstrap/app.php';

// Simuler une requête API
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/v1/sms/history';
$_GET['api_key'] = 'sk_4dc4c8f0542178c6930acfeec9dd6efa34ef8ed5b8e1e391a391b588';

echo "=== Test d'authentification API ===\n\n";

// Test 1: Vérifier que la clé existe
use Modules\ApiKeys\Models\ApiKey;
$apiKey = ApiKey::where('key', $_GET['api_key'])->first();

if ($apiKey) {
    echo "✓ Clé API trouvée\n";
    echo "  - ID: {$apiKey->id}\n";
    echo "  - User ID: {$apiKey->user_id}\n";
    echo "  - Active: " . ($apiKey->is_active ? 'Oui' : 'Non') . "\n";
    echo "  - Valide: " . ($apiKey->isValid() ? 'Oui' : 'Non') . "\n";
} else {
    echo "✗ Clé API non trouvée\n";
    exit(1);
}

// Test 2: Vérifier l'utilisateur
$user = $apiKey->user()->first();
if ($user) {
    echo "\n✓ Utilisateur trouvé\n";
    echo "  - ID: {$user->id}\n";
    echo "  - Username: {$user->username}\n";
    echo "  - Active: " . ($user->is_active ? 'Oui' : 'Non') . "\n";
} else {
    echo "\n✗ Utilisateur non trouvé\n";
    exit(1);
}

// Test 3: Vérifier les permissions
use Modules\RBAC\Services\PermissionService;
$permissionService = new PermissionService();

echo "\n=== Permissions de l'utilisateur ===\n";
$requiredPermission = 'sms.history.view';

try {
    $hasPermission = $permissionService->userHasPermission($user->id, $requiredPermission);
    echo ($hasPermission ? "✓" : "✗") . " Permission '{$requiredPermission}': " . ($hasPermission ? "OUI" : "NON") . "\n";
} catch (Exception $e) {
    echo "✗ Erreur lors de la vérification: {$e->getMessage()}\n";
}

// Test 4: Lister toutes les permissions SMS de l'utilisateur
echo "\nToutes les permissions SMS:\n";
$allPermissions = $permissionService->getUserPermissions($user->id);
foreach ($allPermissions as $perm) {
    if (strpos($perm, 'sms') !== false) {
        echo "  - {$perm}\n";
    }
}

echo "\n=== Fin du test ===\n";
