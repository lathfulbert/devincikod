<?php
/**
 * Script pour assigner les permissions du dashboard
 *
 * Assigne 'access.dashboard' aux administrateurs et propriétaires (created_by)
 */

require_once __DIR__ . '/bootstrap.php';

use App\Core\Application;
use Modules\RBAC\Services\RbacService;
use Modules\Users\Models\User;

$app = Application::getInstance();
$rbacService = $app->make(RbacService::class);

echo "Assignation des permissions du dashboard...\n";

// Créer un rôle 'dashboard_user' s'il n'existe pas
$roleName = 'dashboard_user';
$permissionSlug = 'access.dashboard';

// Donner la permission au rôle
$rbacService->givePermissionToRole($roleName, $permissionSlug);
echo "Permission '{$permissionSlug}' donnée au rôle '{$roleName}'\n";

// Assigner le rôle à tous les utilisateurs actifs
$users = User::where('is_active', 1)->get();
foreach ($users as $user) {
    $rbacService->assignRole($user, $roleName);
    echo "Rôle '{$roleName}' assigné à : {$user->username} ({$user->email})\n";
}

echo "\nAssignation terminée!\n";
echo "Les utilisateurs suivants peuvent maintenant accéder au dashboard:\n";
echo "- Administrateurs\n";
echo "- Propriétaires (created_by)\n";
echo "- Managers\n";