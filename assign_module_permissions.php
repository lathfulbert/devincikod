<?php
/**
 * Script pour assigner les permissions des modules
 *
 * Assigne les permissions d'accès à tous les modules aux utilisateurs actifs
 */

require_once __DIR__ . '/bootstrap.php';

use App\Core\Application;
use App\Core\Database\Database;
use Modules\RBAC\Services\RbacService;
use Modules\Users\Models\User;

$app = Application::getInstance();
$db = Database::getInstance();
$rbacService = $app->make(RbacService::class);

echo "Assignation des permissions des modules...\n";

// Obtenir tous les modules
$moduleManager = $app->moduleManager;
$modules = $moduleManager->getModules();

// Obtenir tous les rôles existants
$existingRoles = $db->query('SELECT name FROM roles')->fetchAll(PDO::FETCH_COLUMN);

// Donner les permissions d'accès aux modules à tous les rôles
foreach ($existingRoles as $roleName) {
    echo "Assignation des permissions au rôle '{$roleName}' :\n";
    
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
            echo "  ✓ {$permissionSlug}\n";
        } catch (Exception $e) {
            // Permission déjà existante, ignorer
            echo "  - {$permissionSlug} (déjà existante)\n";
        }
    }
    echo "\n";
}

echo "\nAssignation terminée!\n";
echo "Tous les rôles existants ont maintenant les permissions d'accès à tous les modules.\n";