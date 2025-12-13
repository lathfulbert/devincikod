<?php
/**
 * Script pour assigner les permissions des modules
 *
 * Assigne les permissions d'accès à tous les modules aux utilisateurs actifs
 */

require_once __DIR__ . '/bootstrap.php';

use App\Core\Application;
use Modules\RBAC\Services\RbacService;
use Modules\Users\Models\User;

$app = Application::getInstance();
$rbacService = $app->make(RbacService::class);

echo "Assignation des permissions des modules...\n";

// Créer un rôle 'user' s'il n'existe pas
$roleName = 'user';

// Obtenir tous les modules
$moduleManager = $app->moduleManager;
$modules = $moduleManager->getModules();

// Donner les permissions d'accès aux modules au rôle
foreach ($modules as $module) {
    $moduleName = $module->getName();
    // Convertir le nom du module en clé de permission
    $moduleKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $moduleName));
    $moduleKey = str_replace([' ', '-'], '_', $moduleKey);
    $moduleKey = preg_replace('/_+/', '_', $moduleKey);
    $moduleKey = trim($moduleKey, '_');
    
    $permissionSlug = 'access.' . $moduleKey;
    
    try {
        $rbacService->givePermissionToRole($roleName, $permissionSlug);
        echo "Permission '{$permissionSlug}' donnée au rôle '{$roleName}'\n";
    } catch (Exception $e) {
        // Permission déjà existante, ignorer
        echo "Permission '{$permissionSlug}' déjà existante\n";
    }
}

// Assigner le rôle à tous les utilisateurs actifs
$users = User::where('is_active', 1)->get();
foreach ($users as $user) {
    try {
        $rbacService->assignRole($user, $roleName);
        echo "Rôle '{$roleName}' assigné à : {$user->username} ({$user->email})\n";
    } catch (Exception $e) {
        // Rôle déjà assigné, ignorer
        echo "Rôle '{$roleName}' déjà assigné à : {$user->username}\n";
    }
}

echo "\nAssignation terminée!\n";
echo "Tous les utilisateurs actifs ont maintenant accès à tous les modules.\n";