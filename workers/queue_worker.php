#!/usr/bin/env php
<?php
/**
 * Queue Worker: Long-running process
 *
 * Ce worker tourne en continu et traite la file d'attente
 *
 * Démarrage: php workers/queue_worker.php
 * En background: nohup php workers/queue_worker.php > /var/log/queue_worker.log 2>&1 &
 *
 * Supervisord config:
 * [program:sms_queue_worker]
 * command=php /path/to/workers/queue_worker.php
 * autostart=true
 * autorestart=true
 * user=www-data
 * stdout_logfile=/var/log/queue_worker.log
 * stderr_logfile=/var/log/queue_worker_error.log
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Application;
use App\Core\Services\HeartbeatHelper;
use Modules\SmsCore\Services\SmsQueueService;

$app = Application::getInstance();

echo "[" . date('Y-m-d H:i:s') . "] Queue Worker started\n";
echo "Press Ctrl+C to stop\n\n";

$idleSeconds = 0;
$maxIdleSeconds = 60; // Enregistrer heartbeat toutes les 60 secondes même si idle

while (true) {
    try {
        // Traiter un batch de 10 messages
        $results = SmsQueueService::processQueue(10);

        if ($results['processed'] > 0) {
            // Il y a eu du travail - enregistrer le heartbeat
            HeartbeatHelper::ping('queue_worker');
            $idleSeconds = 0;

            echo "[" . date('Y-m-d H:i:s') . "] Processed: {$results['processed']}, " .
                 "Success: {$results['success']}, " .
                 "Failed: {$results['failed']}, " .
                 "Skipped: {$results['skipped']}\n";

            // Petite pause entre chaque batch
            sleep(1);
        } else {
            // Pas de travail - attendre
            $idleSeconds += 5;

            // Enregistrer un heartbeat même en idle toutes les 60 secondes
            if ($idleSeconds >= $maxIdleSeconds) {
                HeartbeatHelper::ping('queue_worker');
                $idleSeconds = 0;
                echo "[" . date('Y-m-d H:i:s') . "] Queue is empty, worker still running...\n";
            }

            sleep(5);
        }
    } catch (Exception $e) {
        echo "[ERROR] " . $e->getMessage() . "\n";
        HeartbeatHelper::recordError('queue_worker', $e->getMessage());

        // Attendre avant de réessayer
        sleep(10);
    }
}
