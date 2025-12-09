#!/usr/bin/env php
<?php
/**
 * Script de test pour le système de heartbeat
 *
 * Usage: php scripts/test_heartbeat.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Services\HeartbeatHelper;
use App\Core\Services\HealthCheckService;

// Boot the application
$app = new Application(dirname(__DIR__));
$app->boot();

echo "=== Test du système de Heartbeat ===\n\n";

// Test 1: Enregistrer des heartbeats
echo "1. Enregistrement des heartbeats...\n";
HeartbeatHelper::ping('cron_job');
echo "   ✓ Heartbeat 'cron_job' enregistré\n";

HeartbeatHelper::ping('queue_worker');
echo "   ✓ Heartbeat 'queue_worker' enregistré\n";

sleep(1);

// Test 2: Vérifier l'état des services
echo "\n2. Vérification de l'état des services...\n";
$cronHealth = HealthCheckService::checkServiceHealth('cron_job');
echo "   Cron Job: {$cronHealth['status']} - {$cronHealth['message']}\n";
echo "     Dernière exécution: {$cronHealth['last_run']}\n";
echo "     Exécutions aujourd'hui: {$cronHealth['runs_today']}\n";

$queueHealth = HealthCheckService::checkServiceHealth('queue_worker');
echo "   Queue Worker: {$queueHealth['status']} - {$queueHealth['message']}\n";
echo "     Dernière exécution: {$queueHealth['last_run']}\n";
echo "     Exécutions aujourd'hui: {$queueHealth['runs_today']}\n";

// Test 3: Vérifier l'état global
echo "\n3. État global du système...\n";
$allServices = HealthCheckService::checkAllServices();
echo "   Statut global: " . strtoupper($allServices['global_status']) . "\n";

// Test 4: Récupérer le résumé API
echo "\n4. Résumé API (format JSON)...\n";
$summary = HealthCheckService::getHealthSummary();
echo json_encode($summary, JSON_PRETTY_PRINT) . "\n";

// Test 5: Simuler une erreur
echo "\n5. Test d'enregistrement d'erreur...\n";
HealthCheckService::recordError('cron_job', 'Test error - this is not a real error');
echo "   ✓ Erreur enregistrée\n";

$cronHealthAfterError = HealthCheckService::checkServiceHealth('cron_job');
echo "   Statut après erreur: {$cronHealthAfterError['status']}\n";

// Nettoyer l'erreur de test
HeartbeatHelper::ping('cron_job');
echo "   ✓ Erreur nettoyée avec un nouveau heartbeat\n";

echo "\n=== Tests terminés ===\n";
echo "\nAccédez au dashboard: " . url('/admin/health') . "\n";
echo "API endpoint: " . url('/api/health') . "\n";
