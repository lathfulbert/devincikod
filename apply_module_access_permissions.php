<?php

/**
 * Script d'application des permissions d'accès aux modules
 * 
 * Ce script crée automatiquement les permissions access.<module_key> pour tous les modules
 * et peut les attribuer au rôle admin par défaut.
 * 
 * Usage: php apply_module_access_permissions.php [--assign-to-admin]
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Module\ModuleContract;
use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\RolePermission;
use Modules\RBAC\Services\RbacService;

// Initialiser l'application (sans boot complet pour éviter les routes)
$app = new Application(__DIR__);

// Initialiser manuellement la configuration et la base de données
$basePath = __DIR__;
$app->config->loadDirectory($basePath . '/config');
$defaultConnection = $app->config->get('database.default', 'mysql');
$dbConfig = $app->config->get("database.connections.{$defaultConnection}", []);
\App\Core\Database\Database::getInstance()->connect($dbConfig);

// Options
$assignToAdmin = in_array('--assign-to-admin', $argv);

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Application des Permissions d'Accès aux Modules\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Découvrir tous les modules
echo "📦 Découverte des modules...\n";
$app->moduleManager->discover();
$allModules = $app->moduleManager->getAllModules();

if (empty($allModules)) {
    echo "❌ Aucun module trouvé.\n";
    exit(1);
}

echo "✓ " . count($allModules) . " module(s) trouvé(s)\n\n";

// Fonction helper pour obtenir la clé du module
$getModuleKey = function (ModuleContract $module): string {
    $moduleName = $module->getName();
    
    // Gérer les acronymes courants (AI, RBAC, API, etc.)
    $acronyms = ['AI', 'RBAC', 'API', 'SMS', 'MFA', 'OTP', 'CRM', 'ERP'];
    $normalized = $moduleName;
    
    foreach ($acronyms as $acronym) {
        if (strpos($normalized, $acronym) !== false) {
            // Remplacer l'acronyme par sa version minuscule
            $normalized = str_replace($acronym, strtolower($acronym), $normalized);
        }
    }
    
    // Convert PascalCase to snake_case (mais préserver les acronymes déjà en minuscule)
    $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $normalized));
    // Replace spaces and hyphens with underscores
    $key = str_replace([' ', '-'], '_', $key);
    // Nettoyer les underscores multiples
    $key = preg_replace('/_+/', '_', $key);
    $key = trim($key, '_');
    
    return $key;
};

$db = \App\Core\Database\Database::getInstance();
$created = 0;
$existing = 0;
$errors = 0;

// Vérifier la structure de la table permissions
$columns = $db->query("SHOW COLUMNS FROM permissions")->fetchAll(\PDO::FETCH_COLUMN);
$hasSlug = in_array('slug', $columns);
$hasModuleSlug = in_array('module_slug', $columns);

echo "📋 Création des permissions access.<module_key>...\n\n";

foreach ($allModules as $moduleName => $module) {
    $moduleKey = $getModuleKey($module);
    $permissionSlug = 'access.' . $moduleKey;
    
    try {
        // Vérifier si la permission existe déjà
        $existingPermission = Permission::where('slug', $permissionSlug)->first();
        
        if ($existingPermission) {
            echo "  ⚠  $moduleName → Permission '$permissionSlug' existe déjà\n";
            $existing++;
            continue;
        }
        
        // Créer la permission
        $permission = new Permission();
        $permission->name = ucwords(str_replace(['.', '-', '_'], ' ', $permissionSlug));
        $permission->slug = $permissionSlug;
        $permission->description = "Accès au module {$moduleName}";
        
        if ($hasModuleSlug) {
            $permission->module_slug = $moduleKey;
        }
        
        $permission->save();
        
        echo "  ✓  $moduleName → Permission '$permissionSlug' créée\n";
        $created++;
        
    } catch (\Exception $e) {
        echo "  ✗  $moduleName → Erreur: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Résumé\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✓ Créées: $created\n";
echo "  ⚠ Existantes: $existing\n";
if ($errors > 0) {
    echo "  ✗ Erreurs: $errors\n";
}
echo "\n";

// Attribuer les permissions au rôle admin si demandé
if ($assignToAdmin) {
    echo "🔐 Attribution des permissions au rôle 'admin'...\n\n";
    
    $adminRole = Role::where('slug', 'admin')->first();
    
    if (!$adminRole) {
        echo "  ⚠  Le rôle 'admin' n'existe pas. Création...\n";
        $adminRole = new Role();
        $adminRole->slug = 'admin';
        $adminRole->name = 'Administrateur';
        $adminRole->save();
        echo "  ✓  Rôle 'admin' créé\n\n";
    }
    
    $assigned = 0;
    $alreadyAssigned = 0;
    
    foreach ($allModules as $moduleName => $module) {
        $moduleKey = $getModuleKey($module);
        $permissionSlug = 'access.' . $moduleKey;
        
        try {
            $permission = Permission::where('slug', $permissionSlug)->first();
            
            if (!$permission) {
                echo "  ⚠  $moduleName → Permission '$permissionSlug' non trouvée, ignorée\n";
                continue;
            }
            
            // Vérifier si déjà attribuée
            $exists = RolePermission::where('role_id', $adminRole->id)
                ->where('permission_id', $permission->id)
                ->first();
            
            if ($exists) {
                echo "  ⚠  $moduleName → Permission déjà attribuée au rôle admin\n";
                $alreadyAssigned++;
                continue;
            }
            
            // Attribuer la permission
            $rolePermission = new RolePermission();
            $rolePermission->role_id = $adminRole->id;
            $rolePermission->permission_id = $permission->id;
            $rolePermission->save();
            
            echo "  ✓  $moduleName → Permission attribuée au rôle admin\n";
            $assigned++;
            
        } catch (\Exception $e) {
            echo "  ✗  $moduleName → Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  Attribution au Rôle Admin\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  ✓ Attribuées: $assigned\n";
    echo "  ⚠ Déjà attribuées: $alreadyAssigned\n";
    echo "\n";
}

echo "✅ Terminé!\n\n";
echo "💡 Pour attribuer ces permissions à d'autres rôles, utilisez:\n";
echo "   - Interface admin: /admin/roles\n";
echo "   - Ou via le code: \$rbacService->givePermissionToRole('role_name', 'access.module_key')\n\n";

