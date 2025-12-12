<?php

/**
 * Migration: Créer la table modules
 *
 * Cette migration crée la table modules pour gérer les modules du système
 * et établit la relation avec les permissions via module_slug
 *
 * Date: 2025-12-12
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
        echo "Creating modules table...\n";

        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS modules (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    slug VARCHAR(100) NOT NULL UNIQUE,
                    description TEXT NULL,
                    icon VARCHAR(50) NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    display_order INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                    INDEX idx_slug (slug),
                    INDEX idx_active (is_active),
                    INDEX idx_order (display_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "✓ Modules table created\n";

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
        echo "Dropping modules table...\n";

        try {
            $this->db->query("DROP TABLE IF EXISTS modules");
            echo "✅ Rollback completed successfully!\n";

        } catch (\Exception $e) {
            echo "❌ Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
};
