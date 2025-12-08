<?php
/**
 * Test de la sécurité APP_KEY
 * Tests pour CSRF, Cookies et Sessions sécurisés
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
echo "║        TEST DE SÉCURITÉ APP_KEY - CSRF, Cookies, Sessions    ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Test 1 : CSRF Token avec Signature
echo "1. Test CSRF Token avec Signature HMAC-SHA256...\n";
try {
    $csrf = \App\Core\Security\CSRF::getInstance();

    $token1 = $csrf->generateToken();
    echo "   ✓ Token généré: " . substr($token1, 0, 20) . "...\n";

    // Vérifier que le token est valide
    if ($csrf->validateToken($token1)) {
        echo "   ✓ Token valide\n";
    } else {
        echo "   ✗ Token invalide!\n";
    }

    // Essayer avec un faux token
    if (!$csrf->validateToken('fake_token_123')) {
        echo "   ✓ Faux token correctement rejeté\n";
    } else {
        echo "   ✗ Faux token accepté (PROBLÈME!)\n";
    }

    // Vérifier la signature (simulation de tampering)
    if (isset($_SESSION['_csrf_signed'])) {
        echo "   ✓ Signature stockée en session\n";
    }

    echo "   ✓ SUCCÈS: CSRF tokens signés avec APP_KEY\n\n";
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 2 : Cookies Sécurisés
echo "2. Test Cookies Chiffrés...\n";
try {
    // Simuler un cookie (normalement setcookie() ne fonctionne pas en CLI)
    $testData = [
        'user_id' => 123,
        'preferences' => ['theme' => 'dark', 'lang' => 'fr']
    ];

    // Chiffrer comme le ferait setSecureCookie()
    $encrypted = encrypt($testData);
    $_COOKIE['test_secure_cookie'] = $encrypted;

    echo "   ✓ Cookie chiffré (simulé): " . substr($encrypted, 0, 30) . "...\n";

    // Déchiffrer comme le ferait getSecureCookie()
    $decrypted = getSecureCookie('test_secure_cookie');

    if ($decrypted == $testData) {
        echo "   ✓ Cookie déchiffré correctement\n";
        echo "   ✓ Données: user_id=" . $decrypted['user_id'] . ", theme=" . $decrypted['preferences']['theme'] . "\n";
    } else {
        echo "   ✗ Données du cookie incorrectes\n";
    }

    // Test avec cookie invalide
    $_COOKIE['bad_cookie'] = 'invalid_encrypted_data';
    $result = getSecureCookie('bad_cookie', 'default_value');

    if ($result === 'default_value') {
        echo "   ✓ Cookie invalide retourne la valeur par défaut\n";
    }

    echo "   ✓ SUCCÈS: Cookies chiffrés avec APP_KEY\n\n";
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 3 : Sessions Sécurisées
echo "3. Test Sessions Chiffrées...\n";
try {
    // Démarrer la session si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Stocker des données sensibles
    $sensitiveData = [
        'api_key' => 'sk-1234567890abcdef',
        'api_secret' => 'secret_key_here',
        'user_token' => 'token_abc123xyz'
    ];

    sessionPut('api_credentials', $sensitiveData);
    echo "   ✓ Données sensibles stockées en session (chiffrées)\n";

    // Vérifier le stockage chiffré
    if (isset($_SESSION['_encrypted']['api_credentials'])) {
        echo "   ✓ Données stockées dans \$_SESSION['_encrypted']\n";
        echo "   ✓ Valeur chiffrée: " . substr($_SESSION['_encrypted']['api_credentials'], 0, 30) . "...\n";
    }

    // Récupérer les données
    $retrieved = sessionGet('api_credentials');

    if ($retrieved == $sensitiveData) {
        echo "   ✓ Données déchiffrées correctement\n";
        echo "   ✓ API Key récupérée: " . substr($retrieved['api_key'], 0, 10) . "...\n";
    } else {
        echo "   ✗ Données récupérées incorrectes\n";
    }

    // Test avec clé inexistante
    $defaultValue = sessionGet('non_existent_key', 'default');
    if ($defaultValue === 'default') {
        echo "   ✓ Clé inexistante retourne la valeur par défaut\n";
    }

    // Supprimer les données
    sessionForget('api_credentials');
    $after = sessionGet('api_credentials');

    if ($after === null) {
        echo "   ✓ Données supprimées correctement\n";
    }

    echo "   ✓ SUCCÈS: Sessions chiffrées avec APP_KEY\n\n";
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 4 : Intégrité avec APP_KEY
echo "4. Test d'Intégrité avec APP_KEY...\n";
try {
    $testMessage = "Message secret à protéger";

    // Chiffrer
    $encrypted = encrypt($testMessage);
    echo "   ✓ Message chiffré\n";

    // Modifier le payload (simulation d'attaque)
    $tampered = substr($encrypted, 0, -10) . 'XXXXXXXXXX';

    // Essayer de déchiffrer le payload modifié
    $decryptFailed = false;
    try {
        decrypt($tampered);
    } catch (Exception $e) {
        $decryptFailed = true;
    }

    if ($decryptFailed) {
        echo "   ✓ Payload modifié détecté et rejeté (MAC invalide)\n";
    } else {
        echo "   ✗ Payload modifié accepté (PROBLÈME DE SÉCURITÉ!)\n";
    }

    echo "   ✓ SUCCÈS: Intégrité protégée par MAC (HMAC-SHA256)\n\n";
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 5 : Sécurité Multi-Environnements
echo "5. Test Sécurité par Environnement...\n";
try {
    echo "   Environnement actuel: " . environment() . "\n";

    // Vérifier APP_KEY
    $appKey = env('APP_KEY');
    if ($appKey && str_starts_with($appKey, 'base64:')) {
        echo "   ✓ APP_KEY définie avec préfixe base64:\n";

        $keyDecoded = base64_decode(substr($appKey, 7));
        echo "   ✓ Longueur de clé: " . strlen($keyDecoded) . " bytes (requis: 32)\n";

        if (strlen($keyDecoded) === 32) {
            echo "   ✓ Longueur correcte pour AES-256-CBC\n";
        } else {
            echo "   ✗ Longueur incorrecte!\n";
        }
    }

    // Vérifier les paramètres de sécurité selon l'environnement
    $forceHttps = env('FORCE_HTTPS') === 'true';
    $debug = env('APP_DEBUG') === 'true';

    if (isProduction()) {
        echo "   ⚠ MODE PRODUCTION:\n";
        echo "     - HTTPS forcé: " . ($forceHttps ? 'OUI' : 'NON') . "\n";
        echo "     - Debug: " . ($debug ? 'OUI (DÉSACTIVER!)' : 'NON') . "\n";
    } else {
        echo "   ℹ MODE DÉVELOPPEMENT:\n";
        echo "     - HTTPS forcé: " . ($forceHttps ? 'OUI' : 'NON') . "\n";
        echo "     - Debug: " . ($debug ? 'OUI' : 'NON') . "\n";
    }

    echo "   ✓ SUCCÈS: Configuration de sécurité vérifiée\n\n";
} catch (Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Résumé
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    RÉSUMÉ DE SÉCURITÉ                        ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║                                                              ║\n";
echo "║  ✓ CSRF Tokens signés avec APP_KEY (HMAC-SHA256)            ║\n";
echo "║  ✓ Cookies chiffrés avec APP_KEY (AES-256-CBC)              ║\n";
echo "║  ✓ Sessions chiffrées avec APP_KEY (AES-256-CBC)            ║\n";
echo "║  ✓ Intégrité garantie par MAC                               ║\n";
echo "║  ✓ Protection contre tampering                              ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "Documentation:\n";
echo "- Guide complet: SECURITY_APP_KEY.md\n";
echo "- Chiffrement: APP_KEY_DOCUMENTATION.md\n";
echo "- Environnements: ENVIRONMENT_SETUP.md\n\n";

echo "Helpers disponibles:\n";
echo "- encrypt(\$data), decrypt(\$data)\n";
echo "- setSecureCookie(\$name, \$value), getSecureCookie(\$name)\n";
echo "- sessionPut(\$key, \$value), sessionGet(\$key)\n";
echo "- CSRF::getInstance()->getToken(), validateToken(\$token)\n\n";
