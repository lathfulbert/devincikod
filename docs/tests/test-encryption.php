<?php
/**
 * Test du système de chiffrement APP_KEY
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load .env
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

require_once __DIR__ . '/Core/Support/helpers.php';

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║           TEST DU SYSTÈME DE CHIFFREMENT APP_KEY             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Test 1 : Vérifier APP_KEY
echo "1. Vérification de APP_KEY...\n";
$appKey = env('APP_KEY');
if ($appKey) {
    echo "   ✓ APP_KEY définie: " . substr($appKey, 0, 20) . "...\n\n";
} else {
    echo "   ✗ APP_KEY non définie!\n";
    echo "   Exécutez: php sunu key:generate\n\n";
    exit(1);
}

// Test 2 : Chiffrement de chaîne simple
echo "2. Test de chiffrement de chaîne...\n";
$original = "Données sensibles à protéger";
echo "   Original: '$original'\n";

try {
    $encrypted = encrypt($original);
    echo "   ✓ Chiffré: " . substr($encrypted, 0, 40) . "...\n";

    $decrypted = decrypt($encrypted);
    echo "   ✓ Déchiffré: '$decrypted'\n";

    if ($original === $decrypted) {
        echo "   ✓ SUCCÈS: Données identiques\n\n";
    } else {
        echo "   ✗ ÉCHEC: Données différentes\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 3 : Chiffrement de tableau
echo "3. Test de chiffrement de tableau...\n";
$data = [
    'user_id' => 123,
    'email' => 'user@example.com',
    'token' => 'secret_token_12345'
];
echo "   Original: " . json_encode($data) . "\n";

try {
    $encrypted = encrypt($data);
    echo "   ✓ Chiffré: " . substr($encrypted, 0, 40) . "...\n";

    $decrypted = decrypt($encrypted);
    echo "   ✓ Déchiffré: " . json_encode($decrypted) . "\n";

    if ($data == $decrypted) {
        echo "   ✓ SUCCÈS: Tableaux identiques\n\n";
    } else {
        echo "   ✗ ÉCHEC: Tableaux différents\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 4 : encryptString / decryptString
echo "4. Test de encryptString / decryptString...\n";
$text = "Message secret";
echo "   Original: '$text'\n";

try {
    $encrypted = encryptString($text);
    echo "   ✓ Chiffré: " . substr($encrypted, 0, 40) . "...\n";

    $decrypted = decryptString($encrypted);
    echo "   ✓ Déchiffré: '$decrypted'\n";

    if ($text === $decrypted) {
        echo "   ✓ SUCCÈS: Textes identiques\n\n";
    } else {
        echo "   ✗ ÉCHEC: Textes différents\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 5 : Performance
echo "5. Test de performance...\n";
$iterations = 100;
$start = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $enc = encrypt("Test $i");
    $dec = decrypt($enc);
}

$end = microtime(true);
$duration = ($end - $start) * 1000;
$avgPerOperation = $duration / ($iterations * 2);

echo "   Itérations: $iterations encrypt + $iterations decrypt\n";
echo "   Temps total: " . number_format($duration, 2) . " ms\n";
echo "   Moyenne par opération: " . number_format($avgPerOperation, 3) . " ms\n\n";

// Test 6 : Classe Encrypter directe
echo "6. Test de la classe Encrypter...\n";
try {
    $key = env('APP_KEY');
    if (str_starts_with($key, 'base64:')) {
        $key = base64_decode(substr($key, 7));
    }

    $encrypter = new \App\Core\Encryption\Encrypter($key, 'AES-256-CBC');
    $testData = ['test' => 'direct encrypter'];

    $enc = $encrypter->encrypt($testData);
    $dec = $encrypter->decrypt($enc);

    if ($testData == $dec) {
        echo "   ✓ SUCCÈS: Classe Encrypter fonctionne\n\n";
    } else {
        echo "   ✗ ÉCHEC: Problème avec Encrypter\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    TESTS TERMINÉS                            ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
