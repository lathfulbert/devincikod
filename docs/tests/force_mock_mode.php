<?php
/**
 * Force le passage en mode MOCK (simulation)
 *
 * Ce script supprime les credentials corrompues et force le mode MOCK
 * Usage: php force_mock_mode.php
 */

$pdo = new PDO('mysql:host=localhost;dbname=sunuframework2', 'root', '');

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Forcer le mode MOCK (Simulation) - SunuFramework2    ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Vérifier les credentials actuelles
$stmt = $pdo->query('SELECT id, name, api_key, api_secret FROM sms_gateways WHERE provider_code = "orange_ci"');
$gateway = $stmt->fetch(PDO::FETCH_ASSOC);

echo "État actuel du gateway Orange CI:\n";
echo "==================================\n";
echo "Name: {$gateway['name']}\n";
echo "API Key: " . var_export($gateway['api_key'], true) . "\n";
echo "API Secret: " . var_export($gateway['api_secret'], true) . "\n\n";

if ($gateway['api_key'] === 'VYqHNrIv99ZZfK64PvxGuPqTbBW2sBmp') {
    echo "⚠️  DÉTECTION: Credentials corrompues/de test détectées!\n\n";
    echo "Ces credentials ne fonctionnent PAS avec l'API Orange.\n";
    echo "C'est pourquoi vous voyez \"Failed to obtain access token\".\n\n";
}

// Supprimer les credentials
echo "🔄 Suppression des credentials et passage en mode MOCK...\n";
$stmt = $pdo->prepare('UPDATE sms_gateways SET api_key = NULL, api_secret = NULL WHERE provider_code = "orange_ci"');
$stmt->execute();
echo "✅ Mode MOCK activé!\n\n";

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    MODE MOCK ACTIVÉ                       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "📱 Vous pouvez maintenant:\n";
echo "   1. Tester l'envoi de SMS en mode simulation (sans vraies API)\n";
echo "   2. Les SMS ne seront PAS réellement envoyés\n";
echo "   3. C'est parfait pour le développement et les tests\n\n";

echo "🚀 Pour passer en mode PRODUCTION (SMS réels):\n";
echo "   1. Obtenez vos VRAIES credentials Orange sur:\n";
echo "      → https://developer.orange.com\n";
echo "   2. Créez une application SMS API pour Côte d'Ivoire\n";
echo "   3. Notez votre Client ID et Client Secret\n";
echo "   4. Allez sur: http://localhost/admin/settings/sms\n";
echo "   5. Cliquez \"Éditer\" → Entrez vos VRAIES credentials\n";
echo "   6. Cliquez le bouton \"PROD\" (vert)\n\n";

echo "💡 Tant que vous n'avez pas de vraies credentials Orange,\n";
echo "   utilisez le mode MOCK pour tester votre application.\n\n";
