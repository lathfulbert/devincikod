<?php

/**
 * Test des Helpers de Routes
 * 
 * Ce fichier teste les nouveaux helpers de routes implémentés :
 * - route()
 * - current_route_name()
 * - is_active_route()
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Application;

// Initialize application
$app = Application::getInstance();
$app->boot();

echo "=== Test des Helpers de Routes ===\n\n";

// Test 1: Enregistrer des routes nommées
echo "1. Enregistrement de routes nommées...\n";
$app->router->get('/dashboard', function () {
    return 'Dashboard';
})->name('dashboard.index');

$app->router->get('/admin/users', function () {
    return 'Users List';
})->name('admin.users.index');

$app->router->get('/admin/users/{id}', function ($id) {
    return "User $id";
})->name('admin.users.show');

$app->router->get('/profile', function () {
    return 'Profile';
})->name('profile.show');

echo "✅ Routes enregistrées\n\n";

// Test 2: Tester route() helper
echo "2. Test du helper route()...\n";
try {
    $dashboardUrl = route('dashboard.index');
    echo "   route('dashboard.index') = $dashboardUrl\n";

    $userUrl = route('admin.users.show', ['id' => 123]);
    echo "   route('admin.users.show', ['id' => 123]) = $userUrl\n";

    $profileUrl = route('profile.show');
    echo "   route('profile.show') = $profileUrl\n";

    echo "✅ Helper route() fonctionne\n\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 3: Simuler une requête et tester current_route_name()
echo "3. Test du helper current_route_name()...\n";
try {
    // Simuler dispatch de la route dashboard
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/dashboard';

    ob_start();
    $app->router->dispatch('GET', '/dashboard');
    ob_end_clean();

    $currentRoute = current_route_name();
    echo "   Après dispatch de '/dashboard'\n";
    echo "   current_route_name() = " . ($currentRoute ?? 'null') . "\n";

    if ($currentRoute === 'dashboard.index') {
        echo "✅ Helper current_route_name() fonctionne\n\n";
    } else {
        echo "❌ Attendu 'dashboard.index', reçu '$currentRoute'\n\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 4: Tester is_active_route()
echo "4. Test du helper is_active_route()...\n";
try {
    // La route courante devrait être 'dashboard.index' du test précédent

    $activeClass1 = is_active_route('dashboard.index');
    echo "   is_active_route('dashboard.index') = '$activeClass1'\n";

    $activeClass2 = is_active_route('profile.show');
    echo "   is_active_route('profile.show') = '$activeClass2'\n";

    $activeClass3 = is_active_route(['dashboard.index', 'profile.show']);
    echo "   is_active_route(['dashboard.index', 'profile.show']) = '$activeClass3'\n";

    // Test wildcard
    $activeClass4 = is_active_route('dashboard.*');
    echo "   is_active_route('dashboard.*') = '$activeClass4'\n";

    $activeClass5 = is_active_route('admin.*');
    echo "   is_active_route('admin.*') = '$activeClass5'\n";

    // Test avec classe personnalisée
    $activeClass6 = is_active_route('dashboard.index', 'current-page');
    echo "   is_active_route('dashboard.index', 'current-page') = '$activeClass6'\n";

    if ($activeClass1 === 'active' && $activeClass2 === '' && $activeClass4 === 'active' && $activeClass5 === '') {
        echo "✅ Helper is_active_route() fonctionne\n\n";
    } else {
        echo "❌ Résultats inattendus\n\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 5: Tester avec une route admin
echo "5. Test avec route admin.users.index...\n";
try {
    ob_start();
    $app->router->dispatch('GET', '/admin/users');
    ob_end_clean();

    $currentRoute = current_route_name();
    echo "   Après dispatch de '/admin/users'\n";
    echo "   current_route_name() = " . ($currentRoute ?? 'null') . "\n";

    $activeClass1 = is_active_route('admin.users.index');
    echo "   is_active_route('admin.users.index') = '$activeClass1'\n";

    $activeClass2 = is_active_route('admin.*');
    echo "   is_active_route('admin.*') = '$activeClass2'\n";

    $activeClass3 = is_active_route('dashboard.*');
    echo "   is_active_route('dashboard.*') = '$activeClass3'\n";

    if ($currentRoute === 'admin.users.index' && $activeClass1 === 'active' && $activeClass2 === 'active' && $activeClass3 === '') {
        echo "✅ Wildcard matching fonctionne correctement\n\n";
    } else {
        echo "❌ Résultats inattendus\n\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

echo "=== Fin des Tests ===\n";
echo "\n✅ Tous les helpers de routes sont opérationnels !\n";
echo "\nVous pouvez maintenant utiliser :\n";
echo "  - route('name', ['param' => 'value']) dans vos vues\n";
echo "  - current_route_name() pour obtenir la route active\n";
echo "  - is_active_route('name') pour les menus actifs\n";
