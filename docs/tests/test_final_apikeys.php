<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

$_SESSION['user_id'] = 1;

echo "=== Test Final du Système API Keys ===\n\n";

// Simuler exactement ce qui se passe dans ApiKeyController::index()
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo "✗ Aucun utilisateur en session\n";
    exit(1);
}

$user = \Modules\Users\Models\User::find($userId);

echo "1. Données de l'utilisateur:\n";
echo "   - ID: {$user->id}\n";
echo "   - Username: {$user->username}\n";
echo "   - API Key: " . ($user->api_key ? substr($user->api_key, 0, 20) . "..." : "NULL") . "\n";
echo "   - Créée le: {$user->api_key_created_at}\n";

echo "\n2. Variables passées à la vue:\n";
$viewData = [
    'user' => $user,
    'hasApiKey' => !empty($user->api_key)
];

echo "   - \$user->api_key existe: " . (isset($user->api_key) ? "OUI" : "NON") . "\n";
echo "   - \$hasApiKey: " . var_export($viewData['hasApiKey'], true) . "\n";

echo "\n3. Logique de la vue (simulation):\n";
if ($viewData['hasApiKey']) {
    echo "   ✓ LA VUE AFFICHERA LA CLÉ API\n";
    echo "   ✓ Affichage: Votre Clé API Actuelle\n";
    echo "   ✓ Clé visible: " . substr($user->api_key, 0, 20) . "...\n";
    echo "   ✓ Bouton 'Copier' disponible\n";
    echo "   ✓ Bouton 'Révoquer' disponible\n";
} else {
    echo "   ✗ LA VUE AFFICHERA 'AUCUNE CLÉ API'\n";
    echo "   ✗ Message: Vous n'avez pas encore de clé API\n";
    echo "   ✗ Bouton 'Générer' visible\n";
}

echo "\n4. Test des routes:\n";
$routes = $app->router->getRoutes();
$apiKeyRoutes = array_filter($routes, function($r) {
    return strpos($r['path'], '/admin/api-keys') === 0;
});

foreach ($apiKeyRoutes as $route) {
    echo "   ✓ {$route['method']} {$route['path']}\n";
}

echo "\n=== RÉSULTAT ===\n";
if ($viewData['hasApiKey']) {
    echo "✓✓✓ SUCCÈS TOTAL ✓✓✓\n";
    echo "\nMaintenant dans le navigateur:\n";
    echo "1. Rafraîchissez: http://localhost:81/sunuframework2/admin/api-keys\n";
    echo "2. Vous DEVRIEZ voir:\n";
    echo "   - Votre clé API complète\n";
    echo "   - Un bouton 'Copier'\n";
    echo "   - Un bouton 'Révoquer'\n";
    echo "   - La date de création\n";
} else {
    echo "✗ PROBLÈME: La clé existe en BD mais n'est pas détectée\n";
}
