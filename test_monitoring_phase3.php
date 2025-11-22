<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \App\Core\Application(__DIR__);
$loggingProvider = new \App\Core\Providers\LoggingServiceProvider($app);
$loggingProvider->register();

echo "═════════════════════════════════════════════\n";
echo "   Test du Dashboard Monitoring - Phase 3\n";
echo "═════════════════════════════════════════════\n\n";

// 1. Générer des données de test
echo "📝 Génération de données de test...\n";

try {
    // Logs normaux
    logger('database')->info('Utilisateur connecté', ['user_id' => 1, 'ip' => '127.0.0.1']);
    logger('database')->notice('Cache vidé', ['key' => 'config']);

    // Warnings
    logger('database')->warning('Tentative de connexion échouée', ['ip' => '192.168.1.50']);
    logger('database')->warning('API lente', ['endpoint' => '/api/users', 'duration' => '1500ms']);

    // Erreurs
    logger('database')->error('Erreur de paiement', ['order_id' => 999, 'error' => 'Gateway timeout']);
    logger('database')->critical('Service Redis indisponible');

    echo "   ✅ 6 logs insérés en base de données\n";
} catch (\Exception $e) {
    echo "   ⚠️  Erreur DB: " . $e->getMessage() . "\n";
}

// 2. Vérifier le contrôleur
echo "\n📝 Vérification du Contrôleur...\n";
$controller = new \Modules\Admin\Controllers\MonitoringController();

// Simuler une requête
$_GET['page'] = 1;

// Note: On ne peut pas exécuter index() car il fait un echo de la vue
// Mais on peut vérifier que la classe est instanciable et les méthodes existent
if (method_exists($controller, 'index') && method_exists($controller, 'clear')) {
    echo "   ✅ Contrôleur MonitoringController valide\n";
} else {
    echo "   ❌ Méthodes manquantes dans MonitoringController\n";
}

// 3. Vérifier les fichiers de vue
echo "\n📝 Vérification des Vues...\n";
$viewPath = __DIR__ . '/templates/admin/monitoring/index.php';
if (file_exists($viewPath)) {
    echo "   ✅ Vue index.php trouvée\n";
} else {
    echo "   ❌ Vue index.php manquante\n";
}

echo "\n═════════════════════════════════════════════\n";
echo "🎉 Phase 3 terminée !\n";
echo "👉 Accédez au dashboard : http://localhost:8000/admin/monitoring\n";
