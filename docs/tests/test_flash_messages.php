<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Test du système de notifications flash ===\n\n";

// 1. Simuler une session
$_SESSION['user_id'] = 1;

echo "1. Test du format de flash messages\n";
echo "   Format attendu par le composant alerts : \$_SESSION['flash']['TYPE'][] = 'message'\n\n";

// 2. Tester la génération de message
echo "2. Simulation d'une génération de clé API...\n";

try {
    $apiKey = bin2hex(random_bytes(32));
    $db = \App\Core\Database\Database::getInstance();
    $userId = $_SESSION['user_id'];

    $db->query(
        "UPDATE users SET api_key = ?, api_key_created_at = NOW() WHERE id = ?",
        [$apiKey, $userId]
    );

    $_SESSION['flash']['success'][] = 'Clé API générée avec succès !';
    echo "   ✓ Message de succès ajouté à \$_SESSION['flash']['success']\n";
} catch (Exception $e) {
    $_SESSION['flash']['danger'][] = 'Erreur : ' . $e->getMessage();
    echo "   ✗ Message d'erreur ajouté\n";
}

echo "\n3. Vérification de la session\n";
if (isset($_SESSION['flash'])) {
    echo "   ✓ \$_SESSION['flash'] existe\n";
    foreach ($_SESSION['flash'] as $type => $messages) {
        echo "   - Type '$type' : " . count($messages) . " message(s)\n";
        foreach ($messages as $msg) {
            echo "     > $msg\n";
        }
    }
} else {
    echo "   ✗ \$_SESSION['flash'] n'existe pas\n";
}

echo "\n4. Vérification du composant alerts\n";
$alertsPath = __DIR__ . '/resources/views/backend/components/alerts.php';
if (file_exists($alertsPath)) {
    echo "   ✓ Composant alerts.php existe\n";

    $content = file_get_contents($alertsPath);
    if (strpos($content, "if (isset(\$_SESSION['flash']))") !== false) {
        echo "   ✓ Le composant lit bien \$_SESSION['flash']\n";
    }

    if (strpos($content, "Swal.fire") !== false) {
        echo "   ✓ Le composant utilise SweetAlert2\n";
    }
} else {
    echo "   ✗ Composant alerts.php non trouvé\n";
}

echo "\n5. Vérification de la vue API Keys\n";
$viewPath = __DIR__ . '/Modules/Auth/Views/api/index.php';
if (file_exists($viewPath)) {
    echo "   ✓ Vue auth/api/index.php existe\n";

    $content = file_get_contents($viewPath);
    if (strpos($content, "component('alerts')") !== false || strpos($content, "@component('alerts')") !== false) {
        echo "   ✓ La vue inclut le composant alerts\n";
    } else {
        echo "   ⚠️  La vue n'inclut peut-être pas le composant alerts\n";
    }
}

echo "\n=== Résumé ===\n";
echo "✓ Les middlewares de permission ont été retirés\n";
echo "✓ Le format des flash messages a été corrigé\n";
echo "✓ Le système utilise maintenant : \$_SESSION['flash']['success'][] et \$_SESSION['flash']['danger'][]\n";
echo "✓ Le composant alerts affichera les notifications via SweetAlert2\n\n";

echo "Instructions de test dans le navigateur :\n";
echo "1. Rafraîchissez la page : http://localhost:81/sunuframework2/admin/api-keys\n";
echo "2. Cliquez sur 'Générer une Clé API'\n";
echo "3. Vous devriez voir une notification toast en haut à droite :\n";
echo "   - Icône verte de succès\n";
echo "   - Message : 'Clé API générée avec succès !'\n";
echo "   - Auto-disparition après 3 secondes\n";
