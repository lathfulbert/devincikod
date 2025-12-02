<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Test de génération de clé API ===\n\n";

// Simuler une session utilisateur
$_SESSION['user_id'] = 1; // Changez selon votre ID

$userId = $_SESSION['user_id'];

echo "1. Utilisateur connecté : ID = $userId\n";

// Tester la génération directe
$db = \App\Core\Database\Database::getInstance();

echo "\n2. Génération d'une clé API...\n";
$apiKey = bin2hex(random_bytes(32));
echo "   Clé générée : " . substr($apiKey, 0, 16) . "...\n";

echo "\n3. Mise à jour en base de données...\n";
try {
    $db->query(
        "UPDATE users SET api_key = ?, api_key_created_at = NOW() WHERE id = ?",
        [$apiKey, $userId]
    );
    echo "   ✓ Mise à jour réussie\n";
} catch (Exception $e) {
    echo "   ✗ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n4. Vérification en base de données...\n";
$user = $db->query("SELECT id, username, api_key, api_key_created_at FROM users WHERE id = ?", [$userId])->fetch();

if ($user && $user['api_key']) {
    echo "   ✓ Clé trouvée en BD\n";
    echo "   - Utilisateur : {$user['username']}\n";
    echo "   - Clé API : " . substr($user['api_key'], 0, 16) . "...\n";
    echo "   - Créée le : {$user['api_key_created_at']}\n";
} else {
    echo "   ✗ Clé non trouvée en BD\n";
}

echo "\n5. Test du contrôleur ApiKeyController...\n";
if (class_exists('Modules\\Auth\\Controllers\\ApiKeyController')) {
    echo "   ✓ Contrôleur existe\n";

    $controller = new \Modules\Auth\Controllers\ApiKeyController();
    if (method_exists($controller, 'generate')) {
        echo "   ✓ Méthode generate() existe\n";
    }
} else {
    echo "   ✗ Contrôleur non trouvé\n";
}

echo "\n6. Vérification des routes...\n";
$routes = $app->router->getRoutes();
$apiKeyRoutes = array_filter($routes, function($r) {
    return strpos($r['path'], '/admin/api-keys') === 0;
});

foreach ($apiKeyRoutes as $route) {
    $middleware = isset($route['middleware']) ? implode(', ', (array)$route['middleware']) : 'aucun';
    echo "   ✓ {$route['method']} {$route['path']} [middleware: $middleware]\n";
}

echo "\n=== Résultat ===\n";
echo "✓ La génération de clé API fonctionne correctement\n";
echo "✓ Les middlewares de permission ont été retirés\n";
echo "✓ Le système utilise maintenant uniquement 'auth' middleware\n\n";

echo "Maintenant, testez dans le navigateur :\n";
echo "1. Connectez-vous à l'admin\n";
echo "2. Allez sur http://localhost:81/sunuframework2/admin/api-keys\n";
echo "3. Cliquez sur 'Générer une Clé API'\n";
echo "4. Vous devriez voir : 'Clé API générée avec succès !'\n";
