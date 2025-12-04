<?php

/**
 * Migration: Add Author Tracking Columns
 *
 * Cette migration ajoute les colonnes created_by, updated_by, et deleted_by
 * aux tables existantes qui n'ont pas encore ces colonnes.
 *
 * Exécuter cette migration pour activer le tracking des auteurs sur vos tables.
 *
 * Usage:
 * php public/index.php migrate
 */

use App\Core\Database\Database;

return new class {

    /**
     * Liste des tables à migrer avec leurs colonnes existantes
     * Format: 'nom_table' => [colonnes_à_ajouter]
     */
    private array $tables = [
        // SmsCore Module
        'sms_messages' => ['created_by', 'updated_by'],
        'sms_campaigns' => ['updated_by'], // created_by existe déjà
        'sms_queue' => ['created_by', 'updated_by'],
        'sms_billing_logs' => ['created_by', 'updated_by'],
        'sender_names' => ['created_by', 'updated_by', 'deleted_by'],
        'user_sender_names' => ['created_by'],

        // Wallet Module
        'wallets' => ['created_by', 'updated_by'],
        'wallet_transactions' => ['created_by'],

        // Contacts Module
        'contacts' => ['created_by', 'updated_by', 'deleted_by'],
        'contact_field_definitions' => ['created_by', 'updated_by'],

        // Settings Module
        'settings' => ['created_by', 'updated_by'],
        'sms_gateways' => ['created_by', 'updated_by'],
        'wallet_gateways' => ['created_by', 'updated_by'],
        'translations' => ['created_by', 'updated_by'],
        'translation_history' => ['created_by'],
        'webhooks' => ['created_by', 'updated_by'],
        'webhook_logs' => ['created_by'],

        // Notifications Module
        'notifications' => ['created_by', 'updated_by'],
        'notification_templates' => ['created_by', 'updated_by'],
        'notification_recipients' => ['created_by'],
        'delivery_logs' => ['created_by'],
        'user_notification_preferences' => ['created_by', 'updated_by'],

        // RBAC Module
        'roles' => ['created_by', 'updated_by', 'deleted_by'],
        'permissions' => ['created_by', 'updated_by'],
        'modules' => ['created_by', 'updated_by'],

        // ApiKeys Module
        'api_keys' => ['created_by', 'updated_by', 'deleted_by'],
        'api_request_logs' => ['created_by'],

        // Admin Module
        'cron_tasks' => ['created_by', 'updated_by'],
        'cron_logs' => ['created_by'],
        'jobs' => ['created_by'],
        'failed_jobs' => ['created_by'],
        'cache_config' => ['created_by', 'updated_by'],

        // Backup Module
        'backups' => ['created_by', 'updated_by'],

        // EmailMarketing Module
        'email_campaigns' => ['created_by', 'updated_by'],
        'email_messages' => ['created_by'],
        'email_templates' => ['created_by', 'updated_by', 'deleted_by'],
        'email_logs' => ['created_by'],
        'campaign_logs' => ['created_by'],
        'workflows' => ['created_by', 'updated_by'],
        'workflow_executions' => ['created_by'],
    ];

    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getPdo();

        echo "🔧 Migration: Ajout des colonnes de tracking des auteurs...\n\n";

        foreach ($this->tables as $table => $columns) {
            // Vérifier si la table existe
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() === 0) {
                echo "⚠️  Table '{$table}' n'existe pas, ignorée.\n";
                continue;
            }

            echo "📋 Traitement de la table '{$table}':\n";

            foreach ($columns as $column) {
                // Vérifier si la colonne existe déjà
                $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");

                if ($stmt->rowCount() > 0) {
                    echo "   ✓ Colonne '{$column}' existe déjà\n";
                    continue;
                }

                // Ajouter la colonne
                try {
                    $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` INT UNSIGNED NULL";

                    // Positionner la colonne selon son type
                    if ($column === 'created_by') {
                        // Après created_at si elle existe
                        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE 'created_at'");
                        if ($stmt->rowCount() > 0) {
                            $sql .= " AFTER `created_at`";
                        }
                    } elseif ($column === 'updated_by') {
                        // Après updated_at si elle existe
                        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE 'updated_at'");
                        if ($stmt->rowCount() > 0) {
                            $sql .= " AFTER `updated_at`";
                        }
                    } elseif ($column === 'deleted_by') {
                        // Après deleted_at si elle existe
                        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE 'deleted_at'");
                        if ($stmt->rowCount() > 0) {
                            $sql .= " AFTER `deleted_at`";
                        }
                    }

                    $pdo->exec($sql);
                    echo "   ✅ Colonne '{$column}' ajoutée avec succès\n";

                    // Ajouter l'index pour améliorer les performances
                    $indexName = "idx_{$table}_{$column}";
                    $pdo->exec("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
                    echo "   ✅ Index '{$indexName}' créé\n";

                } catch (\Exception $e) {
                    echo "   ❌ Erreur lors de l'ajout de '{$column}': " . $e->getMessage() . "\n";
                }
            }

            echo "\n";
        }

        echo "✅ Migration terminée avec succès!\n";
        echo "\n📚 N'oubliez pas d'ajouter le trait HasAuthor à vos modèles:\n";
        echo "   use App\\Core\\Database\\Traits\\HasAuthor;\n";
        echo "   use HasAuthor;\n";
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getPdo();

        echo "🔧 Rollback: Suppression des colonnes de tracking des auteurs...\n\n";

        foreach ($this->tables as $table => $columns) {
            // Vérifier si la table existe
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() === 0) {
                continue;
            }

            echo "📋 Traitement de la table '{$table}':\n";

            foreach ($columns as $column) {
                // Vérifier si la colonne existe
                $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");

                if ($stmt->rowCount() === 0) {
                    continue;
                }

                try {
                    // Supprimer l'index d'abord
                    $indexName = "idx_{$table}_{$column}";
                    $pdo->exec("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");

                    // Supprimer la colonne
                    $pdo->exec("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
                    echo "   ✅ Colonne '{$column}' supprimée\n";
                } catch (\Exception $e) {
                    echo "   ⚠️  Erreur: " . $e->getMessage() . "\n";
                }
            }

            echo "\n";
        }

        echo "✅ Rollback terminé!\n";
    }
};
