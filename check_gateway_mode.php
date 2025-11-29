<?php
/**
 * Script de Vérification du Mode Gateway
 *
 * Ce script vérifie si vos gateways sont en mode MOCK ou RÉEL
 * Usage: php check_gateway_mode.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\Settings\Models\SmsGateway;
use Modules\SmsCore\Services\SmsGatewayFactory;

// Initialiser l'application
$app = new Application(__DIR__);
$app->boot();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║        Vérification Mode Gateway - SunuFramework2         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Récupérer les gateways actifs
$gateways = SmsGateway::where('is_active', 1)->get();

if (empty($gateways)) {
    echo "❌ Aucun gateway actif trouvé\n";
    exit(1);
}

foreach ($gateways as $config) {
    echo "Gateway: {$config->name} [{$config->provider_code}]\n";
    echo str_repeat("-", 60) . "\n";

    // Créer l'instance du gateway
    $gateway = SmsGatewayFactory::create($config);

    if (!$gateway) {
        echo "❌ Impossible de créer l'instance du gateway\n\n";
        continue;
    }

    $gatewayClass = get_class($gateway);
    $isMock = strpos($gatewayClass, 'MockGateway') !== false;

    echo "Class: {$gatewayClass}\n";
    echo "Mode : " . ($isMock ? "🧪 SIMULATION (Mock)" : "🚀 PRODUCTION (Réel)") . "\n";

    // Vérifier les credentials
    $hasApiKey = !empty($config->api_key) && strlen(trim($config->api_key)) > 10;
    $hasApiSecret = !empty($config->api_secret) && strlen(trim($config->api_secret)) > 10;

    echo "\nCrédentials:\n";
    echo "  API Key    : " . ($hasApiKey ? "✓ Configurée (" . strlen($config->api_key) . " chars)" : "❌ Non configurée") . "\n";
    echo "  API Secret : " . ($hasApiSecret ? "✓ Configurée (" . strlen($config->api_secret) . " chars)" : "❌ Non configurée") . "\n";
    echo "  Sender ID  : " . ($config->sender_id ? "✓ {$config->sender_id}" : "❌ Non configuré") . "\n";

    // Balance (uniquement pour les vrais gateways, Mock retourne 999.99)
    $balance = $gateway->getBalance();
    echo "\nSolde: " . ($isMock ? "N/A (Mode Mock)" : number_format($balance, 2) . " XOF") . "\n";

    // Recommandations
    if ($isMock && ($hasApiKey && $hasApiSecret)) {
        echo "\n⚠️  WARNING: Credentials configurées mais gateway en mode Mock!\n";
        echo "   Vérifiez que les credentials sont valides (longueur > 10 chars)\n";
    }

    if (!$isMock) {
        echo "\n✅ CE GATEWAY EST EN MODE PRODUCTION\n";
        echo "   Les SMS seront réellement envoyés et facturés!\n";
    } else {
        echo "\n🧪 CE GATEWAY EST EN MODE SIMULATION\n";
        echo "   Les SMS ne seront PAS envoyés, c'est juste une simulation\n";
        echo "   Pour activer le mode réel, configurez vos credentials via:\n";
        echo "   → php configure_orange_sms.php\n";
        echo "   → Ou via l'interface web: /admin/settings/sms\n";
    }

    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "Vérification terminée.\n\n";
