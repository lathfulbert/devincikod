#!/usr/bin/env php
<?php
/**
 * Cron Job: Process SMS Queue
 *
 * Ce script traite la file d'attente des SMS à envoyer
 *
 * Crontab: * * * * * php /path/to/cron/process_sms_queue.php >> /var/log/sms_queue.log 2>&1
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Application;
use App\Core\Services\HeartbeatHelper;
use Modules\SmsCore\Services\SmsQueueService;

// Enregistrer le heartbeat au début
HeartbeatHelper::ping('cron_job');

$app = Application::getInstance();

try {
    echo "[" . date('Y-m-d H:i:s') . "] Starting SMS queue processing...\n";

    // Traiter 50 SMS par exécution
    $results = SmsQueueService::processQueue(50);

    echo "[" . date('Y-m-d H:i:s') . "] Terminé. " .
         "Traités: {$results['processed']}, " .
         "Réussis: {$results['success']}, " .
         "Échoués: {$results['failed']}, " .
         "Ignorés: {$results['skipped']}\n";

    exit(0);

} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";

    // Enregistrer l'erreur dans le monitoring
    HeartbeatHelper::recordError('cron_job', $e->getMessage());

    exit(1);
}
