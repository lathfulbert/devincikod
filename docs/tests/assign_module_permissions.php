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

// Nettoyer les permissions 'access.*' existantes pour tous les rôles
echo "Nettoyage des permissions 'access.*' existantes...\n";
$db->query("DELETE FROM role_permissions WHERE permission_id IN (SELECT id FROM permissions WHERE slug LIKE 'access.%')");
echo "Permissions 'access.*' supprimées de tous les rôles.\n\n";

// Définir quels rôles ont accès à quels modules
$rolePermissions = [
    'Administrateur' => ['admin', 'api_keys', 'auth', 'contacts', 'i18n', 'r_b_a_c', 'settings', 'sms_core', 'users', 'wallet'],
    'Manager' => ['admin', 'api_keys', 'auth', 'contacts', 'i18n', 'settings', 'sms_core', 'users', 'wallet'],
    'Éditeur' => ['auth', 'contacts', 'i18n', 'users'],
    'Rédacteur' => ['auth', 'contacts', 'users'],
    'Utilisateur' => ['auth', 'users'] // Pas d'accès aux modules avancés
];

// Donner les permissions selon les rôles
foreach ($rolePermissions as $roleName => $allowedModules) {
    echo "Assignation des permissions au rôle '{$roleName}' :\n";
    
    foreach ($modules as $module) {
        $moduleName = $module->getName();
        $moduleKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $moduleName));
        $moduleKey = str_replace([' ', '-'], '_', $moduleKey);
        $moduleKey = preg_replace('/_+/', '_', $moduleKey);
        $moduleKey = trim($moduleKey, '_');
        
        if (in_array($moduleKey, $allowedModules)) {
            $permissionSlug = 'access.' . $moduleKey;
            
            try {
                $rbacService->givePermissionToRole($roleName, $permissionSlug);
                echo "  ✓ {$permissionSlug}\n";
            } catch (Exception $e) {
                echo "  - {$permissionSlug} (déjà existante)\n";
            }
        }
    }
    echo "\n";
}

echo "\nAssignation terminée!\n";
echo "Permissions assignées selon la hiérarchie :\n";
echo "- Administrateur : accès à tous les modules\n";
echo "- Manager : accès aux modules principaux sauf RBAC\n";
echo "- Éditeur : accès limité (auth, contacts, i18n, users)\n";
echo "- Rédacteur : accès basique (auth, users)\n";
echo "- Utilisateur : accès minimal (auth, users)\n";