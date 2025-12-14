<?php

/**
 * Migration: Harmonize Wallet Permissions
 *
 * This migration cleans up obsolete wallet permissions and ensures
 * only the permissions used in the current code implementation exist.
 *
 * Run this migration to harmonize wallet permissions in the database.
 */


// Correction du chemin bootstrap
if (!class_exists('App\\Core\\Application')) {
    require_once __DIR__ . '/../../bootstrap.php';
}

class HarmonizeWalletPermissionsMigration
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function up()
    {
        echo "Running HarmonizeWalletPermissionsMigration...\n";

        // Permissions to keep (used in current code)
        $keepPermissions = [
            'access.wallet' => 'Access Wallet',
            'wallet.dashboard' => 'Wallet Dashboard',
            'wallet.manage' => 'Manage Wallets',
            'wallet.topup' => 'Topup Wallet',
            'wallet.requests.view' => 'View Wallet Requests',
            'wallet.requests.manage' => 'Manage Wallet Requests'
        ];

        // Permissions to remove (obsolete)
        $obsoletePermissions = [
            'wallet.create',
            'wallet.debit',
            'wallet.credit',
            'wallet.history.view',
            'wallet.settings.manage',
            'wallet.view',
            'settings.wallet.manage'
        ];

        // Ensure required permissions exist
        echo "Ensuring required permissions exist...\n";
        foreach ($keepPermissions as $slug => $name) {
            $existing = $this->db->query('SELECT id FROM permissions WHERE slug = ?', [$slug])->fetch();
            if (!$existing) {
                $this->db->query(
                    'INSERT INTO permissions (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())',
                    [$name, $slug]
                );
                echo "✓ Created permission: $name ($slug)\n";
            } else {
                echo "✓ Permission already exists: $name ($slug)\n";
            }
        }

        // Remove obsolete permissions
        echo "\nRemoving obsolete permissions...\n";
        foreach ($obsoletePermissions as $slug) {
            $existing = $this->db->query('SELECT id, name FROM permissions WHERE slug = ?', [$slug])->fetch();
            if ($existing) {
                $this->db->query('DELETE FROM permissions WHERE slug = ?', [$slug]);
                echo "✓ Removed obsolete permission: {$existing['name']} ($slug)\n";
            } else {
                echo "✓ Permission already removed: $slug\n";
            }
        }

        echo "\nMigration completed successfully!\n";
        $this->showFinalPermissions();
    }

    public function down()
    {
        echo "Rolling back HarmonizeWalletPermissionsMigration...\n";

        // Restore the old permissions that were removed
        $oldPermissions = [
            'wallet.create' => 'Wallet Create',
            'wallet.debit' => 'Wallet Debit',
            'wallet.credit' => 'Wallet Credit',
            'wallet.history.view' => 'Wallet History View',
            'wallet.settings.manage' => 'Wallet Settings Manage',
            'wallet.view' => 'Wallet View',
            'settings.wallet.manage' => 'Settings Wallet Manage'
        ];

        echo "Restoring old permissions...\n";
        foreach ($oldPermissions as $slug => $name) {
            $existing = $this->db->query('SELECT id FROM permissions WHERE slug = ?', [$slug])->fetch();
            if (!$existing) {
                $this->db->query(
                    'INSERT INTO permissions (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())',
                    [$name, $slug]
                );
                echo "✓ Restored permission: $name ($slug)\n";
            }
        }

        echo "\nRollback completed!\n";
    }

    private function showFinalPermissions()
    {
        $permissions = $this->db->query('SELECT * FROM permissions WHERE slug LIKE ? ORDER BY slug', ['%wallet%'])->fetchAll();
        echo "\nFinal Wallet permissions:\n";
        foreach ($permissions as $perm) {
            echo "- {$perm['name']} (slug: {$perm['slug']})\n";
        }
    }
}

// Run the migration if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? $_SERVER['SCRIPT_FILENAME'] ?? '')) {
    try {
        $migration = new HarmonizeWalletPermissionsMigration();

        $action = $_GET['action'] ?? 'up';
        if ($action === 'down') {
            $migration->down();
        } else {
            $migration->up();
        }
    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}