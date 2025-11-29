<?php
/**
 * Installation Script - SMS Messages Table
 *
 * Ce script crée la table sms_messages pour stocker l'historique des SMS
 * Exécution: php install_sms_messages.php
 */

// Start output buffering to prevent header issues
ob_start();

require_once __DIR__ . '/vendor/autoload.php';

// Require migration file directly (not autoloaded due to numeric prefix)
require_once __DIR__ . '/Modules/SmsCore/Database/Migrations/001_create_sms_messages_table.php';

use App\Core\Application;
use App\Core\Database\Database;
use Modules\SmsCore\Database\Migrations\CreateSmsMessagesTable;

// Initialize application first (starts session)
try {
    $app = new Application(__DIR__);
    $app->boot();
    $db = Database::getInstance();
} catch (\Exception $e) {
    ob_end_clean();
    echo "\n╔═══════════════════════════════════════════════════════════╗\n";
    echo "║                        ERREUR                              ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    echo "❌ Échec d'initialisation: " . $e->getMessage() . "\n";
    echo "📁 Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}

// Now we can output safely
ob_end_clean();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Installation Table SMS Messages - SunuFramework2      ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

try {
    echo "[1/3] Initialisation de l'application...\n";
    echo "✓ Application initialisée\n\n";

    // Vérifier si la table existe déjà
    echo "[2/3] Vérification de la table existante...\n";
    $pdo = $db->getPdo();

    $stmt = $pdo->query("SHOW TABLES LIKE 'sms_messages'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "⚠ Table 'sms_messages' existe déjà\n";
        echo "Voulez-vous la recréer? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $response = trim(fgets($handle));
        fclose($handle);

        if (strtolower($response) !== 'y') {
            echo "\n✓ Installation annulée\n\n";
            exit(0);
        }

        echo "⚠ Suppression de la table existante...\n";
        $migration = new CreateSmsMessagesTable();
        $migration->down();
        echo "✓ Table supprimée\n\n";
    } else {
        echo "✓ Table 'sms_messages' n'existe pas\n\n";
    }

    // Créer la table
    echo "[3/3] Création de la table 'sms_messages'...\n";
    $migration = new CreateSmsMessagesTable();
    $migration->up();
    echo "✓ Table 'sms_messages' créée avec succès\n\n";

    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║                  INSTALLATION RÉUSSIE                      ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    echo "✓ La table sms_messages a été créée avec succès\n";
    echo "✓ Vous pouvez maintenant envoyer des SMS via /admin/sms/send\n\n";

} catch (\Exception $e) {
    echo "\n╔═══════════════════════════════════════════════════════════╗\n";
    echo "║                        ERREUR                              ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📁 Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
