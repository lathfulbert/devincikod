#!/usr/bin/env php
<?php
/**
 * Script de test pour l'API SMS
 *
 * Usage: php test_api.php
 */

// Configuration
$API_KEY = 'VOTRE_CLE_API_ICI';
$BASE_URL = 'http://localhost/api/v1';

// Couleurs pour le terminal
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

function printHeader($text) {
    global $BLUE, $NC;
    echo "\n{$BLUE}========================================{$NC}\n";
    echo "{$BLUE}$text{$NC}\n";
    echo "{$BLUE}========================================{$NC}\n\n";
}

function printSuccess($text) {
    global $GREEN, $NC;
    echo "{$GREEN}✓ $text{$NC}\n";
}

function printError($text) {
    global $RED, $NC;
    echo "{$RED}✗ $text{$NC}\n";
}

function printInfo($text) {
    global $YELLOW, $NC;
    echo "{$YELLOW}ℹ $text{$NC}\n";
}

function makeRequest($method, $endpoint, $data = null) {
    global $API_KEY, $BASE_URL;

    $url = $BASE_URL . $endpoint;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $API_KEY,
        'Content-Type: application/json'
    ]);

    if ($method === 'POST' && $data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Vérifier la clé API
if ($API_KEY === 'VOTRE_CLE_API_ICI') {
    printError("Veuillez configurer votre clé API dans ce script");
    printInfo("Modifiez la variable \$API_KEY avec votre vraie clé API");
    exit(1);
}

printHeader("TEST 1: Vérifier le solde");
$result = makeRequest('GET', '/sms/balance');
if ($result['http_code'] === 200) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        printSuccess("Authentification réussie");
        printInfo("Solde: {$data['data']['balance']} {$data['data']['currency']}");
        printInfo("SMS envoyés: {$data['data']['statistics']['total_sent']}");
    } else {
        printError("Réponse invalide: " . $result['response']);
    }
} else {
    printError("Échec (HTTP {$result['http_code']})");
    if ($result['response']) {
        echo "Réponse: {$result['response']}\n";
    }
    if ($result['error']) {
        echo "Erreur: {$result['error']}\n";
    }
}

printHeader("TEST 2: Envoyer un SMS (TEST)");
printInfo("Note: Remplacez le numéro par un vrai numéro pour un envoi réel");

$smsData = [
    'to' => '+225XXXXXXXX', // Remplacez par un vrai numéro
    'message' => 'Test SMS API - ' . date('H:i:s'),
    'sender_id' => 'TestAPI'
];

printInfo("Destinataire: {$smsData['to']}");
printInfo("Message: {$smsData['message']}");

$result = makeRequest('POST', '/sms/send', $smsData);
if ($result['http_code'] === 200) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        printSuccess("SMS envoyé avec succès");
        printInfo("ID du message: {$data['data']['message_id']}");
        printInfo("Segments: {$data['data']['segments']}");
        printInfo("Coût: {$data['data']['cost']} {$data['data']['currency']}");
    } else {
        printError("Erreur lors de l'envoi: " . ($data['message'] ?? 'Erreur inconnue'));
    }
} else {
    printError("Échec (HTTP {$result['http_code']})");
    if ($result['response']) {
        echo "Réponse: {$result['response']}\n";
    }
}

printHeader("TEST 3: Consulter l'historique");
$result = makeRequest('GET', '/sms/history?limit=5');
if ($result['http_code'] === 200) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        printSuccess("Historique récupéré");
        printInfo("Total: {$data['pagination']['total']} SMS");
        printInfo("Page: {$data['pagination']['page']}/{$data['pagination']['pages']}");

        if (!empty($data['data'])) {
            echo "\nDerniers SMS:\n";
            foreach (array_slice($data['data'], 0, 3) as $sms) {
                echo "  - {$sms['recipient']} | {$sms['status']} | {$sms['cost']} {$sms['currency']} | {$sms['created_at']}\n";
            }
        }
    } else {
        printError("Réponse invalide");
    }
} else {
    printError("Échec (HTTP {$result['http_code']})");
}

printHeader("TEST 4: Historique avec filtres");
$result = makeRequest('GET', '/sms/history?status=paid&limit=3');
if ($result['http_code'] === 200) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        printSuccess("Historique filtré récupéré");
        printInfo("SMS avec status='paid': {$data['pagination']['total']}");
    } else {
        printError("Réponse invalide");
    }
} else {
    printError("Échec (HTTP {$result['http_code']})");
}

printHeader("RÉSUMÉ DES TESTS");
printInfo("Tests terminés");
printInfo("Consultez la documentation complète sur /admin/sms/api/docs");

echo "\n";
