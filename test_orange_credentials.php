<?php
/**
 * Script de test des credentials Orange CI
 *
 * Ce script teste directement vos credentials Orange AVANT de les configurer
 * dans le système. Cela permet de vérifier qu'elles fonctionnent.
 *
 * Usage:
 * php test_orange_credentials.php YOUR_CLIENT_ID YOUR_CLIENT_SECRET
 */

if ($argc < 3) {
    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║       Test Credentials Orange CI - SunuFramework2        ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    echo "Usage: php test_orange_credentials.php CLIENT_ID CLIENT_SECRET\n\n";
    echo "Exemple:\n";
    echo "  php test_orange_credentials.php VYqHNrIv99ZZfK64PvxGuPqTbBW2sBmp sGE3dBtAvTuZUPNvvuF3fAMl8NJ4K8lLR7oYU09DECkt\n\n";
    echo "Où trouver vos credentials:\n";
    echo "  1. Allez sur https://developer.orange.com\n";
    echo "  2. Connectez-vous à votre compte\n";
    echo "  3. Allez dans 'My Apps'\n";
    echo "  4. Sélectionnez votre application SMS API\n";
    echo "  5. Copiez le 'Client ID' et 'Client Secret'\n\n";
    exit(1);
}

$clientId = $argv[1];
$clientSecret = $argv[2];

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║       Test Credentials Orange CI - SunuFramework2        ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "Credentials à tester:\n";
echo "====================\n";
echo "Client ID:     " . substr($clientId, 0, 8) . "..." . substr($clientId, -4) . " (" . strlen($clientId) . " chars)\n";
echo "Client Secret: " . substr($clientSecret, 0, 8) . "..." . substr($clientSecret, -4) . " (" . strlen($clientSecret) . " chars)\n\n";

// Test 1: Obtenir un token OAuth
echo "📡 Test 1: Récupération du token OAuth2...\n";
echo "------------------------------------------------------------\n";

$tokenUrl = 'https://api.orange.com/oauth/v2/token';

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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "cURL Error: " . ($curlError ?: 'Aucune') . "\n";
echo "Response: $response\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $accessToken = $data['access_token'] ?? null;

    if ($accessToken) {
        echo "✅ TOKEN RÉCUPÉRÉ AVEC SUCCÈS!\n";
        echo "Token: " . substr($accessToken, 0, 20) . "..." . substr($accessToken, -10) . "\n";
        echo "Expires in: " . ($data['expires_in'] ?? 'N/A') . " secondes\n\n";

        // Test 2: Essayer d'envoyer un SMS de test
        echo "📡 Test 2: Envoi SMS de test (simulation)...\n";
        echo "------------------------------------------------------------\n";

        // IMPORTANT: Le senderAddress doit être un NUMÉRO DE TÉLÉPHONE VALIDE
        // que vous avez enregistré chez Orange Developer
        $senderPhone = 'tel:+2250000000000'; // ⚠️ REMPLACEZ par votre numéro enregistré chez Orange
        $senderName = 'TICAFRIQUE'; // Nom d'affichage
        $apiUrl = 'https://api.orange.com/smsmessaging/v1/outbound/' . urlencode($senderPhone) . '/requests';

        $payload = [
            'outboundSMSMessageRequest' => [
                'address' => ['tel:+2250749270077'], // Numéro de test
                'senderAddress' => $senderPhone,
                'senderName' => $senderName,
                'outboundSMSTextMessage' => [
                    'message' => 'Test SMS from SunuFramework2'
                ]
            ]
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $smsResponse = curl_exec($ch);
        $smsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "HTTP Code: $smsHttpCode\n";
        echo "Response: $smsResponse\n\n";

        if ($smsHttpCode === 201 || $smsHttpCode === 200) {
            echo "✅ SMS ENVOYÉ AVEC SUCCÈS!\n";
            echo "\n╔═══════════════════════════════════════════════════════════╗\n";
            echo "║              VOS CREDENTIALS SONT VALIDES                 ║\n";
            echo "╚═══════════════════════════════════════════════════════════╝\n\n";
            echo "Prochaine étape:\n";
            echo "  1. Allez sur http://localhost/admin/settings/sms\n";
            echo "  2. Cliquez 'Éditer'\n";
            echo "  3. Entrez ces credentials\n";
            echo "  4. Cliquez 'Mettre à jour'\n";
            echo "  5. Cliquez le bouton 'PROD' pour activer le mode production\n\n";
        } else {
            $smsData = json_decode($smsResponse, true);
            echo "⚠️  ERREUR lors de l'envoi SMS\n";
            echo "Raison possible:\n";
            if ($smsHttpCode === 403) {
                echo "  - Vous n'avez pas souscrit à l'API SMS pour Côte d'Ivoire\n";
                echo "  - Votre Sender ID n'est pas approuvé\n";
                echo "  - Votre compte n'a pas de crédit\n";
            } elseif ($smsHttpCode === 401) {
                echo "  - Le token a expiré (peu probable)\n";
            } else {
                echo "  - Code erreur: " . ($smsData['error'] ?? 'Unknown') . "\n";
                echo "  - Description: " . ($smsData['error_description'] ?? 'N/A') . "\n";
            }
            echo "\nℹ️  Le token est valide, mais l'envoi SMS a échoué.\n";
            echo "   Vérifiez votre configuration sur https://developer.orange.com\n\n";
        }

    } else {
        echo "❌ Token non trouvé dans la réponse\n\n";
    }

} elseif ($httpCode === 401) {
    echo "❌ ERREUR D'AUTHENTIFICATION (HTTP 401)\n\n";
    echo "Vos credentials sont INVALIDES.\n\n";
    echo "Raisons possibles:\n";
    echo "  1. Le Client ID est incorrect\n";
    echo "  2. Le Client Secret est incorrect\n";
    echo "  3. Votre compte Orange Developer est suspendu\n\n";
    echo "Solutions:\n";
    echo "  1. Vérifiez vos credentials sur https://developer.orange.com\n";
    echo "  2. Assurez-vous de copier EXACTEMENT le Client ID et Client Secret\n";
    echo "  3. Régénérez vos credentials si nécessaire\n\n";

} elseif ($httpCode === 404) {
    echo "❌ ENDPOINT NON TROUVÉ (HTTP 404)\n\n";
    echo "L'URL de l'API est incorrecte ou l'API n'est pas disponible.\n\n";

} else {
    echo "❌ ERREUR INCONNUE\n\n";
    echo "HTTP Code: $httpCode\n";
    echo "Contactez le support Orange si le problème persiste.\n\n";
}

echo "Fin du test.\n";
