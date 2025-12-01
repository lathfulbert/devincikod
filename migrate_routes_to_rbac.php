<?php

/**
 * Script de Migration RBAC
 * 
 * Ce script applique automatiquement les middlewares RBAC
 * à toutes les routes de l'application.
 * 
 * Usage: php migrate_routes_to_rbac.php [--dry-run] [--backup]
 */

require_once __DIR__ . '/vendor/autoload.php';

class RbacRouteMigration
{
    private array $report = [];
    private bool $dryRun = false;
    private bool $createBackup = true;
    private string $backupDir;

    // Mapping routes -> permissions
    private array $routePermissions = [
        // Admin Dashboard
        '/admin' => 'admin.access',
        '/admin/dashboard' => 'admin.access',
        '/admin/monitoring' => 'admin.access',

        // Modules Management (CRITIQUE)
        '/admin/modules' => 'admin.modules.view',
        '/admin/modules/enable' => 'admin.modules.manage',
        '/admin/modules/disable' => 'admin.modules.manage',
        '/admin/modules/install' => 'admin.modules.manage',
        '/admin/modules/uninstall' => 'admin.modules.manage',

        // Users Management
        '/admin/users$' => 'admin.users.view',
        '/admin/users/create' => 'admin.users.create',
        '/admin/users/store' => 'admin.users.create',
        '/admin/users/.*/edit' => 'admin.users.edit',
        '/admin/users/.*/update' => 'admin.users.edit',
        '/admin/users/.*/delete' => 'admin.users.delete',

        // Roles Management
        '/admin/roles$' => 'admin.roles.view',
        '/admin/roles/create' => 'admin.roles.create',
        '/admin/roles/store' => 'admin.roles.create',
        '/admin/roles/.*/edit' => 'admin.roles.edit',
        '/admin/roles/.*/update' => 'admin.roles.edit',
        '/admin/roles/.*/delete' => 'admin.roles.delete',

        // Permissions Management (TRÈS CRITIQUE)
        '/admin/permissions' => 'admin.permissions.view',
        '/admin/permissions/create' => 'admin.permissions.view',
        '/admin/permissions/store' => 'admin.permissions.view',
        '/admin/permissions/.*/edit' => 'admin.permissions.view',
        '/admin/permissions/.*/update' => 'admin.permissions.view',
        '/admin/permissions/.*/delete' => 'admin.permissions.view',

        // Queue Management
        '/admin/queue' => 'queue.view',
        '/admin/queue/jobs' => 'queue.view',
        '/admin/queue/failed' => 'queue.view',
        '/admin/queue/retry' => 'queue.retry',
        '/admin/queue/retry-all' => 'queue.manage',
        '/admin/queue/delete' => 'queue.delete',

        // Cron Management
        '/admin/cron' => 'cron.view',
        '/admin/cron/toggle' => 'cron.manage',
        '/admin/cron/run' => 'cron.execute',

        // Profile
        '/admin/profile' => 'auth.profile.view',
        '/admin/profile/update' => 'auth.profile.edit',
        '/admin/profile/change-password' => 'auth.profile.edit',
        '/admin/profile/update-password' => 'auth.profile.edit',

        // API Keys
        '/admin/api-keys' => 'apikeys.view',
        '/admin/api-keys/generate' => 'apikeys.create',
        '/admin/api-keys/regenerate' => 'apikeys.create',
        '/admin/api-keys/revoke' => 'apikeys.revoke',
    ];

    // Routes critiques nécessitant double protection
    private array $criticalRoutes = [
        '/admin/modules/install',
        '/admin/modules/uninstall',
        '/admin/permissions/.*/(update|delete)',
        '/admin/users/.*/delete',
        '/admin/roles/.*/delete',
    ];

    public function __construct()
    {
        $this->backupDir = __DIR__ . '/storage/backups/routes_' . date('Y-m-d_His');

        // Parse command line arguments
        global $argv;
        $this->dryRun = in_array('--dry-run', $argv ?? []);
        $this->createBackup = !in_array('--no-backup', $argv ?? []);
    }

    public function run(): void
    {
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║         Script de Migration RBAC - Routes Security           ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";

        if ($this->dryRun) {
            echo "⚠️  MODE DRY-RUN: Aucun fichier ne sera modifié.\n\n";
        }

        // Étape 1: Créer les sauvegardes
        if ($this->createBackup && !$this->dryRun) {
            $this->createBackups();
        }

        // Étape 2: Migrer les routes Admin
        $this->migrateAdminRoutes();

        // Étape 3: Migrer les routes Auth
        $this->migrateAuthRoutes();

        // Étape 4: Migrer les routes API
        $this->migrateApiRoutes();

        // Étape 5: Générer le rapport
        $this->generateReport();
    }

    private function createBackups(): void
    {
        echo "📦 Création des sauvegardes...\n";

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $filesToBackup = [
            __DIR__ . '/Modules/Admin/Routes/web.php',
            __DIR__ . '/Modules/Auth/Routes/web.php',
            __DIR__ . '/Modules/Notifications/routes/api.php',
            __DIR__ . '/routes/web.php',
        ];

        foreach ($filesToBackup as $file) {
            if (file_exists($file)) {
                $backupPath = $this->backupDir . '/' . basename(dirname($file)) . '_' . basename($file);
                copy($file, $backupPath);
                echo "  ✅ Sauvegardé: " . basename($file) . "\n";
            }
        }

        echo "  📁 Sauvegardes dans: {$this->backupDir}\n\n";
    }

    private function migrateAdminRoutes(): void
    {
        echo "🔧 Migration des routes Admin...\n";

        $filePath = __DIR__ . '/Modules/Admin/Routes/web.php';
        if (!file_exists($filePath)) {
            echo "  ⚠️  Fichier non trouvé: {$filePath}\n";
            return;
        }

        $content = file_get_contents($filePath);
        $originalContent = $content;
        $modifiedCount = 0;

        // Remplacer AuthMiddleware par les nouveaux middlewares RBAC
        $patterns = [
            // Dashboard routes
            [
                'pattern' => '/\$router->get\(\'\/admin\/dashboard\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->get('/admin/dashboard', [AdminController::class, 'dashboard'])\n    ->middleware('can:admin.access');",
            ],
            [
                'pattern' => '/\$router->get\(\'\/admin\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->get('/admin', [AdminController::class, 'index'])\n    ->middleware('can:admin.access');",
            ],

            // Module management (CRITIQUE - double protection)
            [
                'pattern' => '/\$router->post\(\'\/admin\/modules\/install\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/modules/install', [ModuleController::class, 'install'])\n    ->middleware('role:admin')\n    ->middleware('can:admin.modules.manage');",
            ],
            [
                'pattern' => '/\$router->post\(\'\/admin\/modules\/uninstall\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/modules/uninstall', [ModuleController::class, 'uninstall'])\n    ->middleware('role:admin')\n    ->middleware('can:admin.modules.manage');",
            ],

            // Users - View
            [
                'pattern' => '/\$router->get\(\'\/admin\/users\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->get('/admin/users', [UserController::class, 'index'])\n    ->middleware('can:admin.users.view');",
            ],
            // Users - Create
            [
                'pattern' => '/\$router->post\(\'\/admin\/users\/store\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/users/store', [UserController::class, 'store'])\n    ->middleware('can:admin.users.create');",
            ],
            // Users - Delete
            [
                'pattern' => '/\$router->post\(\'\/admin\/users\/\{id\}\/delete\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/users/{id}/delete', [UserController::class, 'delete'])\n    ->middleware('can:admin.users.delete');",
            ],

            // Roles
            [
                'pattern' => '/\$router->get\(\'\/admin\/roles\',.*?\[\$authMiddleware\]\);(?!.*edit)/s',
                'replacement' => "\$router->get('/admin/roles', [RoleController::class, 'index'])\n    ->middleware('can:admin.roles.view');",
            ],

            // Permissions (TRÈS CRITIQUE - super-admin only)
            [
                'pattern' => '/\$router->post\(\'\/admin\/permissions\/\{id\}\/update\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/permissions/{id}/update', [PermissionController::class, 'update'])\n    ->middleware('role:admin');",
            ],

            // Queue
            [
                'pattern' => '/\$router->get\(\'\/admin\/queue\',.*?\[\$authMiddleware\]\);(?!.*\/)/s',
                'replacement' => "\$router->get('/admin/queue', [QueueController::class, 'index'])\n    ->middleware('can:queue.view');",
            ],

            // Cron
            [
                'pattern' => '/\$router->get\(\'\/admin\/cron\',.*?\[\$authMiddleware\]\);(?!.*\/)/s',
                'replacement' => "\$router->get('/admin/cron', [CronController::class, 'index'])\n    ->middleware('can:cron.view');",
            ],
            [
                'pattern' => '/\$router->post\(\'\/admin\/cron\/run\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/cron/run', [CronController::class, 'runManually'])\n    ->middleware('can:cron.execute');",
            ],
        ];

        foreach ($patterns as $replacement) {
            $newContent = preg_replace($replacement['pattern'], $replacement['replacement'], $content);
            if ($newContent !== $content) {
                $modifiedCount++;
                $content = $newContent;
            }
        }

        if ($modifiedCount > 0) {
            if (!$this->dryRun) {
                file_put_contents($filePath, $content);
            }
            $this->report[] = "✅ Admin Routes: {$modifiedCount} routes mises à jour";
            echo "  ✅ {$modifiedCount} routes migrées\n";
        } else {
            echo "  ℹ️  Aucune modification nécessaire\n";
        }
    }

    private function migrateAuthRoutes(): void
    {
        echo "\n🔧 Migration des routes Auth...\n";

        $filePath = __DIR__ . '/Modules/Auth/Routes/web.php';
        if (!file_exists($filePath)) {
            echo "  ⚠️  Fichier non trouvé\n";
            return;
        }

        $content = file_get_contents($filePath);
        $modifiedCount = 0;

        // Profile routes
        $patterns = [
            [
                'pattern' => '/\$router->get\(\'\/admin\/profile\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->get('/admin/profile', [ProfileController::class, 'edit'])\n    ->middleware('can:auth.profile.view');",
            ],
            [
                'pattern' => '/\$router->post\(\'\/admin\/profile\/update\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/profile/update', [ProfileController::class, 'update'])\n    ->middleware('can:auth.profile.edit');",
            ],
            // API Keys
            [
                'pattern' => '/\$router->get\(\'\/admin\/api-keys\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->get('/admin/api-keys', [ApiKeyController::class, 'index'])\n    ->middleware('can:apikeys.view');",
            ],
            [
                'pattern' => '/\$router->post\(\'\/admin\/api-keys\/generate\',.*?\[\$authMiddleware\]\);/s',
                'replacement' => "\$router->post('/admin/api-keys/generate', [ApiKeyController::class, 'generate'])\n    ->middleware('can:apikeys.create');",
            ],
        ];

        foreach ($patterns as $replacement) {
            $newContent = preg_replace($replacement['pattern'], $replacement['replacement'], $content);
            if ($newContent !== $content) {
                $modifiedCount++;
                $content = $newContent;
            }
        }

        if ($modifiedCount > 0) {
            if (!$this->dryRun) {
                file_put_contents($filePath, $content);
            }
            $this->report[] = "✅ Auth Routes: {$modifiedCount} routes mises à jour";
            echo "  ✅ {$modifiedCount} routes migrées\n";
        } else {
            echo "  ℹ️  Aucune modification nécessaire\n";
        }
    }

    private function migrateApiRoutes(): void
    {
        echo "\n🔧 Migration des routes API...\n";

        $filePath = __DIR__ . '/Modules/Notifications/routes/api.php';
        if (!file_exists($filePath)) {
            echo "  ⚠️  Fichier non trouvé\n";
            return;
        }

        // Note: Les routes API nécessitent ApiAuthMiddleware + RBAC
        echo "  ⚠️  Routes API nécessitent une révision manuelle (ApiAuthMiddleware requis)\n";
        $this->report[] = "⚠️  Routes API: Révision manuelle requise";
    }

    private function generateReport(): void
    {
        echo "\n╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    RAPPORT DE MIGRATION                       ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";

        foreach ($this->report as $line) {
            echo $line . "\n";
        }

        if ($this->dryRun) {
            echo "\n⚠️  MODE DRY-RUN: Aucun fichier n'a été modifié.\n";
            echo "   Relancez sans --dry-run pour appliquer les changements.\n";
        } else {
            echo "\n✅ Migration terminée avec succès!\n";
            echo "📁 Sauvegardes: {$this->backupDir}\n";
        }

        echo "\n📋 PROCHAINES ÉTAPES:\n";
        echo "1. Vérifier les fichiers de routes modifiés\n";
        echo "2. Tester l'accès aux différentes routes\n";
        echo "3. Assigner les permissions appropriées aux rôles\n";
        echo "4. Réviser manuellement les routes API\n";

        // Créer un fichier de rapport
        if (!$this->dryRun) {
            $reportPath = __DIR__ . '/migration_report_' . date('Y-m-d_His') . '.txt';
            file_put_contents($reportPath, implode("\n", $this->report));
            echo "\n📄 Rapport sauvegardé: {$reportPath}\n";
        }
    }
}

// Exécution du script
try {
    $migration = new RbacRouteMigration();
    $migration->run();
} catch (\Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
