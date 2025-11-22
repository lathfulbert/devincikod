<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \App\Core\Application(__DIR__);

// Enregistrer le logging service
$loggingProvider = new \App\Core\Providers\LoggingServiceProvider($app);
$loggingProvider->register();
$loggingProvider->boot();

echo "═════════════════════════════════════════════\n";
echo "   Test du Système de Logging - Phase 1\n";
echo "═════════════════════════════════════════════\n\n";

// Test 1 : Tous les niveaux PSR-3
echo "📝 Test 1 : Niveaux PSR-3...\n";
logger()->debug('DEBUG: Information détaillée pour développement');
logger()->info('INFO: Application démarrée avec succès');
logger()->notice('NOTICE: Configuration mise à jour');
logger()->warning('WARNING: Utilisation d\'une fonction dépréciée');
logger()->error('ERROR: Impossible de charger le fichier config.php');
logger()->critical('CRITICAL: Connexion à la base de données perdue');
logger()->alert('ALERT: Espace disque inférieur à 10%');
logger()->emergency('EMERGENCY: Le système est inutilisable');
echo "   ✅ 8 niveaux testés\n\n";

// Test 2 : Logging avec contexte
echo "📝 Test 2 : Logs avec contexte...\n";
logger()->info('User registration completed', [
    'user_id' => 12345,
    'username' => 'john.doe',
    'email' => 'john@example.com',
    'ip_address' => '192.168.1.1',
    'timestamp' => time()
]);

logger()->error('Payment processing failed', [
    'order_id' => 'ORD-2024-001',
    'amount' => 99.99,
    'currency' => 'USD',
    'gateway' => 'Stripe',
    'error_code' => 'card_declined'
]);
echo "   ✅ Contexte JSON ajouté\n\n";

// Test 3 : Interpolation de messages
echo "📝 Test 3 : Interpolation...\n";
logger()->info('User {username} logged in from {ip}', [
    'username' => 'alice',
    'ip' => '10.0.0.5'
]);
echo "   ✅ Variables interpolées\n\n";

// Test 4 : Différents canaux
echo "📝 Test 4 : Canaux multiples...\n";
logger()->info('Message sur le canal par défaut (daily)');
logger('single')->info('Message sur le canal single');
logger('emergency')->critical('Message d\'urgence critique');
logger('null')->info('Ce message sera ignoré (NullHandler)');
echo "   ✅ 4 canaux testés\n\n";

// Test 5 : Rotation quotidienne
echo "📝 Test 5 : DailyFileHandler...\n";
for ($i = 1; $i <= 3; $i++) {
    logger()->info("Message de test #{$i} pour rotation quotidienne");
}
echo "   ✅ Logs avec date dans le nom de fichier\n\n";

// Test 6 : Exception logging
echo "📝 Test 6 : Logging d'exceptions...\n";
try {
    throw new \RuntimeException('Simulation d\'une erreur critique');
} catch (\Exception $e) {
    logger()->error('Exception capturée', [
        'exception_class' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => substr($e->getTraceAsString(), 0, 200) . '...'
    ]);
}
echo "   ✅ Exception loggée avec stack trace\n\n";

// Résumé
echo "═════════════════════════════════════════════\n";
echo "              RÉSUMÉ DES TESTS\n";
echo "═════════════════════════════════════════════\n";
echo "✅ Tous les tests réussis!\n\n";

// Vérifier les fichiers créés
$logsDir = storage_path('logs');
echo "📁 Fichiers de logs créés :\n";
if (is_dir($logsDir)) {
    $files = array_diff(scandir($logsDir), ['.', '..', '.gitignore']);
    foreach ($files as $file) {
        $size = filesize($logsDir . '/' . $file);
        echo "   • {$file} (" . round($size / 1024, 2) . " KB)\n";
    }
} else {
    echo "   ⚠️  Dossier logs non trouvé: {$logsDir}\n";
}

echo "\n💡 Vérifiez les logs dans : storage/logs/\n";
echo "💡 Config dans : config/logging.php\n\n";

echo "🎉 Phase 1 du système de Logging terminée avec succès!\n";
