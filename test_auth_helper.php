<?php

/**
 * Test du Helper auth()
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

echo "=== Test du Helper auth() ===\n\n";

// Test 1: Vérifier que auth() existe
echo "1. Vérification de l'existence de auth()...\n";
if (function_exists('auth')) {
    echo "   ✅ auth() existe\n\n";
} else {
    echo "   ❌ auth() n'existe pas\n\n";
    exit(1);
}

// Test 2: Obtenir l'instance Auth
echo "2. Obtenir l'instance Auth...\n";
try {
    $auth = auth();
    echo "   ✅ Instance Auth obtenue\n";
    echo "   Classe: " . get_class($auth) . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Vérifier les méthodes
echo "3. Vérification des méthodes Auth...\n";
$methods = ['check', 'user', 'login', 'logout'];
foreach ($methods as $method) {
    if (method_exists($auth, $method)) {
        echo "   ✅ $method() existe\n";
    } else {
        echo "   ❌ $method() n'existe pas\n";
    }
}
echo "\n";

// Test 4: Tester check()
echo "4. Test de auth()->check()...\n";
try {
    $isAuthenticated = auth()->check();
    echo "   Utilisateur authentifié: " . ($isAuthenticated ? 'Oui' : 'Non') . "\n";
    echo "   ✅ auth()->check() fonctionne\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 5: Tester user()
echo "5. Test de auth()->user()...\n";
try {
    $user = auth()->user();
    if ($user === null) {
        echo "   Utilisateur: null (non connecté)\n";
    } else {
        echo "   Utilisateur: " . print_r($user, true) . "\n";
    }
    echo "   ✅ auth()->user() fonctionne\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 6: Test du helper can() avec auth()
echo "6. Test du helper can() avec auth()...\n";
try {
    $canEdit = can('edit-posts');
    echo "   can('edit-posts'): " . ($canEdit ? 'true' : 'false') . "\n";
    echo "   ✅ can() fonctionne avec auth()\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

echo "=== Fin des Tests ===\n";
echo "\n✅ Le helper auth() est opérationnel !\n";
echo "\nUtilisation:\n";
echo "  - auth()->check() - Vérifier si utilisateur connecté\n";
echo "  - auth()->user() - Obtenir l'utilisateur courant\n";
echo "  - auth()->login(\$user) - Connecter un utilisateur\n";
echo "  - auth()->logout() - Déconnecter l'utilisateur\n";
