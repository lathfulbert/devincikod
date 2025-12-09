#!/usr/bin/env php
<?php
/**
 * Health Check - Vérifie et répare les SMS bloqués
 *
 * À exécuter toutes les 5 minutes via cron
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Services\HeartbeatHelper;

$startTime = microtime(true);

try {
    // Enregistrer le heartbeat
    HeartbeatHelper::ping('health_check');

    $db = \App\Core\Database\Database::getInstance()->getPdo();

    // 1. Vérifier les SMS bloqués en "processing" depuis plus de 10 minutes
    $stmt = $db->query("
        SELECT COUNT(*) as count
        FROM sms_queue
        WHERE status = 'processing'
        AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    $stuck = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

    if ($stuck > 0) {
        // Réinitialiser les SMS bloqués
        $stmt = $db->prepare("
            UPDATE sms_queue
            SET status = 'pending',
                attempts = attempts + 1
            WHERE status = 'processing'
            AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND attempts < 3
        ");
        $stmt->execute();
        $reset = $stmt->rowCount();

        echo "[" . date('Y-m-d H:i:s') . "] Reset $reset stuck messages (out of $stuck)\n";

        // Marquer comme échoués ceux avec 3+ tentatives
        $stmt = $db->query("
            UPDATE sms_queue
            SET status = 'failed',
                error_message = 'Max attempts reached after being stuck'
            WHERE status = 'processing'
            AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND attempts >= 3
        ");
        $failed = $stmt->rowCount();

        if ($failed > 0) {
            echo "[" . date('Y-m-d H:i:s') . "] Marked $failed messages as failed\n";
        }
    }

    // 2. Mettre à jour les campagnes avec des messages traités
    $stmt = $db->query("
        SELECT DISTINCT campaign_id
        FROM sms_queue
        WHERE campaign_id IS NOT NULL
        AND updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $campaigns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($campaigns as $row) {
        \Modules\SmsCore\Services\SmsQueueService::updateCampaignStats($row['campaign_id']);
    }

    if (count($campaigns) > 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Updated " . count($campaigns) . " campaign(s)\n";
    }

    // 3. Stats générales
    $stmt = $db->query("
        SELECT
            status,
            COUNT(*) as count
        FROM sms_queue
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
        GROUP BY status
    ");
    $stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo "[" . date('Y-m-d H:i:s') . "] Queue stats (last 24h): ";
    foreach ($stats as $stat) {
        echo $stat['status'] . '=' . $stat['count'] . ' ';
    }
    echo "\n";

    $duration = round(microtime(true) - $startTime, 2);
    echo "[" . date('Y-m-d H:i:s') . "] Health check completed in {$duration}s\n";

    exit(0);

} catch (\Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    HeartbeatHelper::recordError('health_check', $e->getMessage());
    exit(1);
}
