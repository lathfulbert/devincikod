<?php

/**
 * Migration: Ajouter les champs module_slug et module_id à la table permissions
 *
 * Cette migration harmonise la table permissions en ajoutant:
 * - module_slug: slug du module (ex: 'sms-core', 'wallet', 'users')
 * - module_id: ID du module (pour référence future si table modules créée)
 *
 * Date: 2025-12-11
 */

use App\Core\Database\Database;

return new class {
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Exécuter la migration
     */
    public function up(): void
    {
        echo "Adding module_slug and module_id columns to permissions table...\n";

        try {
            // Ajouter la colonne module_slug
            $this->db->query("
                ALTER TABLE permissions
                ADD COLUMN IF NOT EXISTS module_slug VARCHAR(100) NULL AFTER module,
                ADD INDEX idx_module_slug (module_slug)
            ");
            echo "✓ Added module_slug column\n";

            // Ajouter la colonne module_id
            $this->db->query("
                ALTER TABLE permissions
                ADD COLUMN IF NOT EXISTS module_id BIGINT UNSIGNED NULL AFTER module_slug,
                ADD INDEX idx_module_id (module_id)
            ");
            echo "✓ Added module_id column\n";

            // Migrer les données existantes: créer module_slug à partir de module
            $this->db->query("
                UPDATE permissions
                SET module_slug = CASE
                    WHEN module IS NOT NULL THEN LOWER(REPLACE(module, ' ', '-'))
                    ELSE NULL
                END
                WHERE module_slug IS NULL
            ");
            echo "✓ Migrated existing data to module_slug\n";

            echo "✅ Migration completed successfully!\n";

        } catch (\Exception $e) {
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        echo "Removing module_slug and module_id columns from permissions table...\n";

        try {
            $this->db->query("ALTER TABLE permissions DROP COLUMN IF EXISTS module_id");
            $this->db->query("ALTER TABLE permissions DROP COLUMN IF EXISTS module_slug");

            echo "✅ Rollback completed successfully!\n";

        } catch (\Exception $e) {
            echo "❌ Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
};
