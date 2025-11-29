<?php
/**
 * Installation Script - SMS & Wallet Modules
 *
 * Ce script installe les tables et données par défaut pour les modules SMS et Wallet
 * Exécution: php install_sms_wallet.php
 */

// Start output buffering to prevent header issues
ob_start();

require_once __DIR__ . '/vendor/autoload.php';

// Require migration files directly (not autoloaded due to numeric prefix)
require_once __DIR__ . '/Modules/Settings/Database/Migrations/002_create_sms_gateways_table.php';
require_once __DIR__ . '/Modules/Settings/Database/Migrations/003_create_wallet_gateways_table.php';

use App\Core\Application;
use App\Core\Database\Database;
use Modules\Settings\Database\Migrations\CreateSmsGatewaysTable;
use Modules\Settings\Database\Migrations\CreateWalletGatewaysTable;
use Modules\Settings\Database\Seeders\SmsGatewaySeeder;
use Modules\Settings\Database\Seeders\WalletGatewaySeeder;

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
echo "║     Installation Modules SMS & Wallet - SunuFramework2    ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

try {
    echo "[1/6] Initialisation de l'application...\n";
    echo "✓ Application initialisée\n\n";

    // Vérifier si les tables existent déjà
    echo "[2/6] Vérification des tables existantes...\n";
    $pdo = $db->getPdo();

    // Vérifier table SMS
    $stmt = $pdo->query("SHOW TABLES LIKE 'sms_gateways'");
    $smsTableExists = $stmt->rowCount() > 0;

    // Vérifier table Wallet
    $stmt = $pdo->query("SHOW TABLES LIKE 'wallet_gateways'");
    $walletTableExists = $stmt->rowCount() > 0;

    if ($smsTableExists) {
        echo "⚠ Table 'sms_gateways' existe déjà\n";
    }
    if ($walletTableExists) {
        echo "⚠ Table 'wallet_gateways' existe déjà\n";
    }
    echo "\n";

    // Exécuter la migration SMS
    if (!$smsTableExists) {
        echo "[3/6] Création de la table 'sms_gateways'...\n";
        $smsMigration = new CreateSmsGatewaysTable();
        $smsMigration->up();
        echo "✓ Table 'sms_gateways' créée avec succès\n\n";
    } else {
        echo "[3/6] Table 'sms_gateways' déjà existante (skip)\n\n";
    }

    // Exécuter la migration Wallet
    if (!$walletTableExists) {
        echo "[4/6] Création de la table 'wallet_gateways'...\n";
        $walletMigration = new CreateWalletGatewaysTable();
        $walletMigration->up();
        echo "✓ Table 'wallet_gateways' créée avec succès\n\n";
    } else {
        echo "[4/6] Table 'wallet_gateways' déjà existante (skip)\n\n";
    }

    // Seeder SMS
    echo "[5/6] Insertion des gateways SMS par défaut...\n";
    $smsSeeder = new SmsGatewaySeeder();
    $smsSeeder->run();
    echo "✓ Gateways SMS insérés\n\n";

    // Seeder Wallet
    echo "[6/6] Insertion des gateways Wallet par défaut...\n";
    $walletSeeder = new WalletGatewaySeeder();
    $walletSeeder->run();
    echo "✓ Gateways Wallet insérés\n\n";

    // Résumé
    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║                  INSTALLATION TERMINÉE                     ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";

    echo "📊 Tables créées:\n";
    echo "   • sms_gateways\n";
    echo "   • wallet_gateways\n\n";

    echo "🎯 Prochaines étapes:\n";
    echo "   1. Accédez à /admin/settings/sms pour configurer les gateways SMS\n";
    echo "   2. Accédez à /admin/settings/wallet pour configurer les gateways Wallet\n";
    echo "   3. Configurez vos clés API pour chaque provider\n\n";

    echo "✅ Installation réussie !\n\n";

} catch (\Exception $e) {
    echo "\n";
    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║                        ERREUR                              ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    echo "❌ " . $e->getMessage() . "\n";
    echo "📁 Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}
