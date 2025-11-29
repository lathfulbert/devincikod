<?php
/**
 * Script pour trouver la bonne URL OAuth Token d'Orange
 *
 * Ce script teste plusieurs URLs possibles pour trouver celle qui fonctionne
 */

if ($argc < 3) {
    echo "Usage: php find_orange_token_url.php CLIENT_ID CLIENT_SECRET\n";
    exit(1);
}

$clientId = $argv[1];
$clientSecret = $argv[2];

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Recherche URL Token OAuth - Orange CI                ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Liste des URLs possibles pour Orange CI
$tokenUrls = [
    'https://api.orange.com/oauth/v2/token',
    'https://api.orange.com/oauth/v3/token',
    'https://api.orange.com/oauth/token',
    'https://apiauth.orange.com/oauth/v2/token',
    'https://apiauth.orange.com/oauth/v3/token',
];

echo "Test des URLs OAuth possibles...\n";
echo "==================================\n\n";

$workingUrl = null;

foreach ($tokenUrls as $index => $tokenUrl) {
    echo ($index + 1) . ". Test: $tokenUrl\n";
    echo "   ";

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        echo "❌ Erreur cURL: $curlError\n\n";
        continue;
    }

    echo "HTTP $httpCode - ";

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            echo "✅ TOKEN OBTENU!\n";
            echo "   Token: " . substr($data['access_token'], 0, 20) . "...\n";
            echo "   Expire: " . ($data['expires_in'] ?? 'N/A') . " secondes\n\n";
            $workingUrl = $tokenUrl;
            break; // On a trouvé!
        } else {
            echo "⚠️  Réponse valide mais pas de token\n";
            echo "   Response: $response\n\n";
        }
    } elseif ($httpCode === 401) {
        echo "🔐 URL existe mais credentials invalides\n";
        echo "   Response: $response\n\n";
        // C'est un bon signe - l'URL existe, mais les credentials sont peut-être incorrectes
    } elseif ($httpCode === 404) {
        echo "❌ URL n'existe pas (404)\n\n";
    } else {
        echo "⚠️  Code inattendu\n";
        echo "   Response: $response\n\n";
    }
}

echo "\n╔═══════════════════════════════════════════════════════════╗\n";

if ($workingUrl) {
    echo "║                    ✅ URL TROUVÉE!                        ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    echo "URL OAuth qui fonctionne: $workingUrl\n\n";
    echo "Prochaines étapes:\n";
    echo "  1. Utilisez cette URL dans votre configuration\n";
    echo "  2. La base de données sera mise à jour automatiquement\n\n";

    // Mettre à jour automatiquement la configuration
    echo "Mise à jour de la configuration...\n";
    $pdo = new PDO('mysql:host=localhost;dbname=sunuframework2', 'root', '');
    $newConfig = json_encode([
        'auth_type' => 'oauth2',
        'token_url' => $workingUrl,
        'country_code' => '+225'
    ]);

    $stmt = $pdo->prepare('UPDATE sms_gateways SET configuration = ? WHERE provider_code = "orange_ci"');
    $stmt->execute([$newConfig]);

    echo "✅ Configuration mise à jour!\n";

} else {
    echo "║                 ❌ AUCUNE URL TROUVÉE                     ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    echo "Aucune URL OAuth valide n'a été trouvée.\n\n";
    echo "Raisons possibles:\n";
    echo "  1. Vos credentials sont incorrectes\n";
    echo "  2. Votre compte Orange Developer n'est pas activé\n";
    echo "  3. Vous n'avez pas souscrit à l'API SMS CI\n";
    echo "  4. L'API Orange utilise une URL différente\n\n";
    echo "Solutions:\n";
    echo "  1. Vérifiez vos credentials sur https://developer.orange.com\n";
    echo "  2. Vérifiez que vous avez bien souscrit à 'SMS API - Côte d'Ivoire'\n";
    echo "  3. Vérifiez le status de votre application (Active/Pending)\n";
    echo "  4. Contactez le support Orange si le problème persiste\n\n";
}
