<?php
/**
 * Script de test pour l'API SMS
 *
 * Ce script teste:
 * 1. Génération d'une clé API
 * 2. Envoi d'un SMS via l'API
 * 3. Vérification de la déduction du crédit
 * 4. Récupération de l'historique
 * 5. Vérification du solde
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\Auth\Models\User;
use Modules\Wallet\Services\WalletService;

// Bootstrap application
$app = new Application(__DIR__);
$app->boot();

echo "=== Test API SMS ===\n\n";

// 1. Trouver un utilisateur de test ou créer
echo "1. Recherche d'un utilisateur de test...\n";
$user = User::where('is_active', 1)->first();

if (!$user) {
    echo "   ❌ Aucun utilisateur trouvé\n";
    exit(1);
}

echo "   ✓ Utilisateur trouvé: {$user->username} (ID: {$user->id})\n\n";

// 2. Générer une clé API si nécessaire
echo "2. Vérification de la clé API...\n";
if (empty($user->api_key)) {
    $apiKey = bin2hex(random_bytes(32));
    $user->update([
        'api_key' => $apiKey,
        'api_key_created_at' => date('Y-m-d H:i:s')
    ]);
    echo "   ✓ Clé API générée: {$apiKey}\n\n";
} else {
    $apiKey = $user->api_key;
    echo "   ✓ Clé API existante: {$apiKey}\n\n";
}

// 3. Vérifier le solde initial
echo "3. Vérification du solde initial...\n";
$walletService = new WalletService();
$initialBalance = $walletService->getBalance($user->id);
echo "   ✓ Solde initial: {$initialBalance} XOF\n\n";

// 4. Ajouter du crédit si nécessaire
if ($initialBalance < 100) {
    echo "4. Ajout de crédit de test (1000 XOF)...\n";
    $walletService->addCredit($user->id, 1000, "Crédit de test pour API");
    $initialBalance = $walletService->getBalance($user->id);
    echo "   ✓ Nouveau solde: {$initialBalance} XOF\n\n";
}

// 5. Test d'envoi SMS via API
echo "5. Test d'envoi SMS via API...\n";

$url = 'http://localhost:81/sunuframework2/api/v1/sms/send';
$data = [
    'to' => '+225XXXXXXXXX',
    'message' => 'Test API SMS - ' . date('H:i:s'),
    'sender_id' => 'TestAPI'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

echo "   Requête: POST {$url}\n";
echo "   Destinataire: {$data['to']}\n";
echo "   Message: {$data['message']}\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: {$httpCode}\n";

$result = json_decode($response, true);

if ($result && $result['success']) {
    echo "   ✓ SMS envoyé avec succès!\n";
    echo "   - Message ID: " . ($result['data']['message_id'] ?? 'N/A') . "\n";
    echo "   - Segments: " . ($result['data']['segments'] ?? 1) . "\n";
    echo "   - Coût: " . ($result['data']['cost'] ?? 0) . " " . ($result['data']['currency'] ?? 'XOF') . "\n\n";

    $smsCost = $result['data']['cost'];
} else {
    echo "   ❌ Erreur: " . ($result['message'] ?? 'Unknown error') . "\n";
    echo "   Réponse: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    exit(1);
}

// 6. Vérifier la déduction du crédit
echo "6. Vérification de la déduction du crédit...\n";
$newBalance = $walletService->getBalance($user->id);
$deducted = $initialBalance - $newBalance;

echo "   Solde avant: {$initialBalance} XOF\n";
echo "   Solde après: {$newBalance} XOF\n";
echo "   Déduit: {$deducted} XOF\n";

if ($deducted == $smsCost) {
    echo "   ✓ Déduction correcte! ({$smsCost} XOF)\n\n";
} else {
    echo "   ⚠️ Attention: déduction ({$deducted}) différente du coût annoncé ({$smsCost})\n\n";
}

// 7. Test récupération historique
echo "7. Test récupération de l'historique via API...\n";
$url = 'http://localhost:81/sunuframework2/api/v1/sms/history?limit=5';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($result && $result['success']) {
    echo "   ✓ Historique récupéré!\n";
    echo "   Total de SMS: " . $result['pagination']['total'] . "\n";
    echo "   Derniers 5 SMS:\n";

    foreach ($result['data'] as $sms) {
        echo "     - {$sms['recipient']} | {$sms['status']} | {$sms['cost']} XOF | {$sms['created_at']}\n";
    }
    echo "\n";
} else {
    echo "   ❌ Erreur: " . ($result['message'] ?? 'Unknown error') . "\n\n";
}

// 8. Test récupération du solde
echo "8. Test récupération du solde via API...\n";
$url = 'http://localhost:81/sunuframework2/api/v1/sms/balance';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result && $result['success']) {
    echo "   ✓ Solde récupéré!\n";
    echo "   Solde: {$result['data']['balance']} {$result['data']['currency']}\n";
    echo "   Statistiques:\n";
    echo "     - Total envoyés: {$result['data']['statistics']['total_sent']}\n";
    echo "     - Total échoués: {$result['data']['statistics']['total_failed']}\n";
    echo "     - Coût total: {$result['data']['statistics']['total_cost']} XOF\n\n";
} else {
    echo "   ❌ Erreur: " . ($result['message'] ?? 'Unknown error') . "\n\n";
}

echo "=== Test terminé avec succès! ===\n";
