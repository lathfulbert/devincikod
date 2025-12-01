<?php

/**
 * Script d'Assignation Rapide du Rôle Admin
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

use Modules\RBAC\Services\RbacService;
use Modules\Users\Models\User;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         Assignation Rapide du Rôle Admin                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Récupérer le premier utilisateur
$user = User::first();

if (!$user) {
    echo "❌ Aucun utilisateur trouvé dans la base de données.\n";
    echo "   Créez d'abord un utilisateur via la page d'inscription.\n";
    exit(1);
}

echo "👤 Utilisateur trouvé:\n";
echo "   - ID: {$user->id}\n";
echo "   - Username: {$user->username}\n";
echo "   - Email: " . ($user->email ?? 'N/A') . "\n\n";

// Assigner le rôle admin
$rbacService = new RbacService();

try {
    $rbacService->assignRole($user, 'admin');
    echo "✅ Rôle 'admin' assigné avec succès!\n\n";

    // Vérifier
    if ($rbacService->hasRole($user, 'admin')) {
        echo "✓ Vérification: L'utilisateur a bien le rôle 'admin'\n";
    }

    echo "\n📋 Permissions du rôle admin: 63 permissions\n";
    echo "   - Accès complet au dashboard\n";
    echo "   -

 Gestion users/roles/permissions\n";
    echo "   - Gestion modules\n";
    echo "   - Et plus encore...\n\n";

    echo "🚀 Vous pouvez maintenant vous connecter et accéder à /admin/dashboard\n";
} catch (\Exception $e) {
    echo "❌ Erreur lors de l'assignation: " . $e->getMessage() . "\n";
    exit(1);
}
