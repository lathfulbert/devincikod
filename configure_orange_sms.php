<?php
/**
 * Script de Configuration Orange SMS API
 *
 * Ce script configure automatiquement le gateway Orange CI avec vos credentials
 * Usage: php configure_orange_sms.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;
use Modules\Settings\Models\SmsGateway;

// Initialiser l'application
$app = new Application(__DIR__);
$app->boot();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Configuration Gateway Orange SMS - SunuFramework2     ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Demander les credentials
echo "Entrez vos credentials Orange Developer Portal:\n\n";

echo "Client ID (API Key): ";
$apiKey = trim(fgets(STDIN));

echo "Client Secret (API Secret): ";
$apiSecret = trim(fgets(STDIN));

echo "Sender ID (ex: TICAFRIQUE): ";
$senderId = trim(fgets(STDIN));

if (empty($apiKey) || empty($apiSecret) || empty($senderId)) {
    echo "\n❌ Erreur: Tous les champs sont obligatoires\n";
    exit(1);
}

// Mettre à jour le gateway
echo "\nConfiguration du gateway Orange CI...\n";

$gateway = SmsGateway::where('provider_code', 'orange_ci')->first();

if (!$gateway) {
    echo "❌ Gateway Orange CI introuvable\n";
    exit(1);
}

// Mettre à jour avec les nouvelles credentials
$pdo = Database::getInstance()->getPdo();

$stmt = $pdo->prepare("
    UPDATE sms_gateways
    SET api_key = ?, api_secret = ?, sender_id = ?, is_active = 1, is_default = 1
    WHERE provider_code = 'orange_ci'
");

$stmt->execute([$apiKey, $apiSecret, $senderId]);

echo "✓ Gateway Orange CI configuré avec succès\n\n";

// Vérifier
$gateway = SmsGateway::where('provider_code', 'orange_ci')->first();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    CONFIGURATION RÉUSSIE                   ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "Gateway : {$gateway->name}\n";
echo "Provider: {$gateway->provider_code}\n";
echo "API URL : {$gateway->api_url}\n";
echo "Sender  : {$gateway->sender_id}\n";
echo "Active  : " . ($gateway->is_active ? "Oui" : "Non") . "\n";
echo "Default : " . ($gateway->is_default ? "Oui" : "Non") . "\n\n";

echo "✅ Vous pouvez maintenant envoyer de vrais SMS via /admin/sms/send\n";
echo "⚠️  Le système utilisera OrangeCIGateway (mode RÉEL)\n";
echo "💰 Les SMS seront débités de votre compte Orange Developer\n\n";
