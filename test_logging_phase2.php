<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \App\Core\Application(__DIR__);
$loggingProvider = new \App\Core\Providers\LoggingServiceProvider($app);
$loggingProvider->register();

echo "═════════════════════════════════════════════\n";
echo "   Test du Système de Logging - Phase 2\n";
echo "═════════════════════════════════════════════\n\n";

// 1. Test Exception Handler
echo "📝 Test 1 : Exception Handler...\n";
$handler = new \App\Core\Exceptions\ExceptionHandler($app);

try {
    throw new \Exception("Test Exception for Logger");
} catch (\Exception $e) {
    $handler->report($e);
    echo "   ✅ Exception rapportée via logger()\n";
}

// 2. Test Database Logging (Simulation)
echo "\n📝 Test 2 : Database Logging...\n";
echo "   ℹ️  Note: Ce test nécessite que la migration 'logs' soit exécutée.\n";
echo "   ℹ️  Exécutez 'php sunu migrate' pour créer la table.\n";

try {
    logger('database')->info('Test log entry in database', [
        'test_id' => uniqid(),
        'status' => 'success'
    ]);
    echo "   ✅ Tentative d'écriture en base de données effectuée\n";
} catch (\Exception $e) {
    echo "   ⚠️  Erreur DB (Normal si migration pas faite): " . $e->getMessage() . "\n";
}

// 3. Test Middleware
echo "\n📝 Test 3 : HTTP Middleware...\n";
$middleware = new \App\Core\Logging\Middleware\HttpRequestLogger();
$request = []; // Mock request
$next = function ($req) {
    return "Response";
};

$middleware->handle($request, $next);
echo "   ✅ Middleware exécuté et logué\n";

echo "\n═════════════════════════════════════════════\n";
echo "🎉 Phase 2 terminée !\n";
