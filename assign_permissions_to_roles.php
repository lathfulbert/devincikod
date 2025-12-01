<?php

/**
 * Script d'Assignation Automatique des Permissions
 * 
 * Ce script assigne toutes les permissions nécessaires aux rôles
 * après la migration RBAC des routes.
 * 
 * Usage: php assign_permissions_to_roles.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use Modules\RBAC\Services\RbacService;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\Permission;

class PermissionAssigner
{
    private RbacService $rbacService;
    private array $report = [];

    // Définition des permissions par rôle
    private array $rolePermissions = [
        'admin' => [
            // Dashboard & Admin
            'admin.access',
            'admin.settings.view',
            'admin.settings.edit',

            // Modules (CRITIQUE)
            'admin.modules.view',
            'admin.modules.manage',

            // Users
            'admin.users.view',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',

            // Roles
            'admin.roles.view',
            'admin.roles.create',
            'admin.roles.edit',
            'admin.roles.delete',

            // Permissions (ADMIN ONLY)
            'admin.permissions.view',

            // Queue
            'queue.view',
            'queue.manage',
            'queue.retry',
            'queue.delete',

            // Cron
            'cron.view',
            'cron.manage',
            'cron.execute',

            // Auth
            'auth.profile.view',
            'auth.profile.edit',

            // API Keys
            'apikeys.view',
            'apikeys.create',
            'apikeys.revoke',
            'apikeys.manage',

            // Backup
            'backup.view',
            'backup.create',
            'backup.download',
            'backup.delete',
            'backup.restore',

            // Settings
            'settings.view',
            'settings.edit',
            'settings.sms.manage',
            'settings.wallet.manage',

            // Contacts
            'contacts.view',
            'contacts.create',
            'contacts.edit',
            'contacts.delete',
            'contacts.export',
            'contacts.import',
            'contacts.fields.manage',
            'contacts.groups.manage',

            // Notifications
            'notifications.view',
            'notifications.send',
            'notifications.templates.manage',
            'notifications.settings.manage',
            'notifications.delete',

            // I18n
            'i18n.view',
            'i18n.edit',
            'i18n.languages.manage',

            // SMS
            'sms.send',
            'sms.history.view',
            'sms.gateways.manage',
            'sms.templates.manage',
            'sms.stats.view',

            // Wallet
            'wallet.view',
            'wallet.create',
            'wallet.debit',
            'wallet.credit',
            'wallet.history.view',
            'wallet.settings.manage',
        ],

        'user' => [
            // Profil uniquement
            'auth.profile.view',
            'auth.profile.edit',

            // Permissions de base
            'contacts.view',
            'notifications.view',
            'sms.send',
            'wallet.view',
        ],

        'editor' => [
            // Profil
            'auth.profile.view',
            'auth.profile.edit',

            // Contacts
            'contacts.view',
            'contacts.create',
            'contacts.edit',

            // Notifications
            'notifications.view',
            'notifications.send',

            // SMS
            'sms.send',
            'sms.history.view',

            // Wallet (lecture seule)
            'wallet.view',
            'wallet.history.view',
        ],
    ];

    public function __construct()
    {
        $this->rbacService = new RbacService();
    }

    public function run(): void
    {
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║      Assignation Automatique des Permissions aux Rôles       ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";

        // Vérifier que les tables existent
        if (!$this->checkDatabaseReady()) {
            echo "❌ ERREUR: Base de données non prête. Lancez les migrations d'abord.\n";
            return;
        }

        // Créer les rôles s'ils n'existent pas
        $this->ensureRolesExist();

        // Assigner les permissions
        $this->assignPermissions();

        // Rapport final
        $this->generateReport();
    }

    private function checkDatabaseReady(): bool
    {
        try {
            Role::count();
            Permission::count();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function ensureRolesExist(): void
    {
        echo "🔧 Vérification des rôles...\n";

        foreach (array_keys($this->rolePermissions) as $roleName) {
            $role = Role::where('slug', $roleName)->first();

            if (!$role) {
                echo "  ➕ Création du rôle: {$roleName}\n";
                $role = new Role();
                $role->slug = $roleName;
                $role->name = ucfirst($roleName);
                $role->description = "Rôle {$roleName}";
                $role->save();

                $this->report[] = "Rôle créé: {$roleName}";
            } else {
                echo "  ✅ Rôle existe: {$roleName}\n";
            }
        }

        echo "\n";
    }

    private function assignPermissions(): void
    {
        echo "🔐 Assignation des permissions...\n\n";

        foreach ($this->rolePermissions as $roleName => $permissions) {
            echo "📋 Rôle: {$roleName}\n";

            $assignedCount = 0;
            $skippedCount = 0;
            $notFoundCount = 0;

            foreach ($permissions as $permissionSlug) {
                $permission = Permission::where('slug', $permissionSlug)->first();

                if (!$permission) {
                    echo "  ⚠️  Permission non trouvée: {$permissionSlug}\n";
                    $notFoundCount++;
                    continue;
                }

                // Vérifier si déjà assignée
                if ($this->rbacService->roleHasPermission($roleName, $permissionSlug)) {
                    $skippedCount++;
                    continue;
                }

                // Assigner la permission
                try {
                    $this->rbacService->givePermissionToRole($roleName, $permissionSlug);
                    $assignedCount++;
                } catch (\Exception $e) {
                    echo "  ❌ Erreur assignation {$permissionSlug}: " . $e->getMessage() . "\n";
                }
            }

            echo "  ✅ Assignées: {$assignedCount} | Déjà présentes: {$skippedCount}";

            if ($notFoundCount > 0) {
                echo " | ⚠️  Non trouvées: {$notFoundCount}";
            }

            echo "\n\n";

            $this->report[] = "[{$roleName}] Assignées: {$assignedCount}, Skipped: {$skippedCount}, Not Found: {$notFoundCount}";
        }
    }

    private function generateReport(): void
    {
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    RAPPORT D'ASSIGNATION                      ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";

        foreach ($this->report as $line) {
            echo $line . "\n";
        }

        echo "\n✅ Assignation terminée!\n\n";

        echo "📋 VÉRIFICATIONS:\n";
        echo "1. Vérifier qu'un utilisateur admin existe\n";
        echo "2. Assigner le rôle 'admin' à cet utilisateur\n";
        echo "3. Tester l'accès aux routes admin\n\n";

        // Statistiques finales
        $this->printStatistics();
    }

    private function printStatistics(): void
    {
        echo "📊 STATISTIQUES:\n";

        foreach (array_keys($this->rolePermissions) as $roleName) {
            $role = Role::where('slug', $roleName)->first();
            if ($role) {
                // Utiliser avec() pour charger la relation
                $role = Role::with('permissions')->where('slug', $roleName)->first();
                $count = $role->permissions ? count($role->permissions->toArray()) : 0;
                echo "  - {$roleName}: {$count} permissions\n";
            }
        }

        echo "\n";
    }
}

// Bootstrap l'application
$app = new \App\Core\Application(__DIR__);
$app->boot();

// Exécution du script
try {
    $assigner = new PermissionAssigner();
    $assigner->run();
} catch (\Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
