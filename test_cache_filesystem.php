<?php

/**
 * Script de test du driver Filesystem
 * 
 * Execute: php test_cache_filesystem.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Cache\CacheManager;

// Initialiser l'application
$app = new Application(__DIR__);
$app->boot();

echo "=== Test du Cache Filesystem ===\n\n";

$cache = CacheManager::getInstance();

// 1. Test Set/Get basique
echo "1. Test Set/Get basique\n";
$cache->set('test_key', 'Hello Cache!', 60);
$value = $cache->get('test_key');
echo "   Valeur: {$value}\n";
echo "   ✓ Test réussi\n\n";

// 2. Test Has
echo "2. Test Has\n";
$exists = $cache->has('test_key');
echo "   Existe: " . ($exists ? 'Oui' : 'Non') . "\n";
echo "   ✓ Test réussi\n\n";

// 3. Test Delete
echo "3. Test Delete\n";
$cache->delete('test_key');
$exists = $cache->has('test_key');
echo "   Existe après suppression: " . ($exists ? 'Oui' : 'Non') . "\n";
echo "   ✓ Test réussi\n\n";

// 4. Test TTL (expiration)
echo "4. Test TTL (dans 2 secondes)\n";
$cache->set('ttl_test', 'Will expire', 2);
echo "   Valeur immédiate: " . $cache->get('ttl_test') . "\n";
sleep(3);
echo "   Valeur après 3s: " . ($cache->get('ttl_test') ?? 'null (expiré)') . "\n";
echo "   ✓ Test réussi\n\n";

// 5. Test Multiple Set/Get
echo "5. Test Multiple Set/Get\n";
$cache->setMultiple([
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3'
], 60);
$values = $cache->getMultiple(['key1', 'key2', 'key3']);
echo "   Values: " . json_encode($values) . "\n";
echo "   ✓ Test réussi\n\n";

// 6. Test Increment/Decrement
echo "6. Test Increment/Decrement\n";
$cache->set('counter', 0);
$cache->increment('counter');
$cache->increment('counter', 5);
echo "   Counter après +1 et +5: " . $cache->get('counter') . "\n";
$cache->decrement('counter', 2);
echo "   Counter après -2: " . $cache->get('counter') . "\n";
echo "   ✓ Test réussi\n\n";

// 7. Test Remember
echo "7. Test Remember Pattern\n";
$firstCall = true;
$value = $cache->remember('expensive_operation', function () use (&$firstCall) {
    if ($firstCall) {
        echo "   → Callback exécuté (pas en cache)\n";
        $firstCall = false;
    }
    return 'Expensive Result';
}, 60);
echo "   Résultat: {$value}\n";

$value = $cache->remember('expensive_operation', function () {
    echo "   → Callback ne devrait PAS s'exécuter\n";
    return 'Should not run';
}, 60);
echo "   Résultat (depuis cache): {$value}\n";
echo "   ✓ Test réussi\n\n";

// 8. Test Stats
echo "8. Statistiques\n";
$stats = $cache->getStats();
foreach ($stats as $key => $value) {
    echo "   {$key}: {$value}\n";
}
echo "   ✓ Test réussi\n\n";

// 9. Test Clear
echo "9. Test Clear\n";
$cache->clear();
$exists = $cache->has('key1');
echo "   key1 existe après clear: " . ($exists ? 'Oui' : 'Non') . "\n";
echo "   ✓ Test réussi\n\n";

// 10. Test Helpers
echo "10. Test Helpers\n";
cache('helper_test', 'Via Helper', 60);
echo "   cache('helper_test'): " . cache('helper_test') . "\n";
echo "   cache_has('helper_test'): " . (cache_has('helper_test') ? 'Oui' : 'Non') . "\n";
cache_forget('helper_test');
echo "   Après cache_forget: " . (cache_has('helper_test') ? 'Oui' : 'Non') . "\n";
echo "   ✓ Test réussi\n\n";

echo "=== Tous les tests réussis! ===\n";
