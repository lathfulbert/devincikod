<?php
// Migration pour ajouter la colonne created_by à la table users (style natif du framework)

use App\Core\Database\Database;

return new class {
    public function up(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getPdo();

        // Vérifier si la colonne existe déjà
        $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'created_by'");
        if ($stmt->rowCount() === 0) {
            // Ajouter la colonne après id
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `created_by` BIGINT UNSIGNED NULL AFTER `id`");
            $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_users_created_by` (`created_by`)");
            echo "✅ Colonne 'created_by' ajoutée à users\n";
        } else {
            echo "ℹ️  Colonne 'created_by' existe déjà dans users\n";
        }
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getPdo();

        // Vérifier si la colonne existe
        $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'created_by'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE `users` DROP INDEX `idx_users_created_by`");
            $pdo->exec("ALTER TABLE `users` DROP COLUMN `created_by`");
            echo "✅ Colonne 'created_by' supprimée de users\n";
        } else {
            echo "ℹ️  Colonne 'created_by' absente de users\n";
        }
    }
};
