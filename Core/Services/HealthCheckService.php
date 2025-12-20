<?php

namespace App\Core\Services;

use App\Core\Database\Database;

class HealthCheckService
{
    /**
     * Get database connection
     */
    private static function getDb(): \PDO
    {
        $pdo = Database::getInstance()->getPdo();
        // ...debug supprimé...
        return $pdo;
    }

    /**
     * Enregistrer que le service a tourné
     */
    public static function recordHeartbeat(string $serviceName): void
    {
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $db = self::getDb();

        // Récupérer l'enregistrement actuel
        $stmt = $db->prepare("SELECT * FROM system_health_checks WHERE service_name = ?");
        $stmt->execute([$serviceName]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Vérifier si c'est un nouveau jour
            $lastRunDate = $existing['last_run_at'] ? date('Y-m-d', strtotime($existing['last_run_at'])) : null;
            $runsToday = ($lastRunDate === $today) ? $existing['runs_today'] + 1 : 1;

            // Mettre à jour
            $stmt = $db->prepare("
                UPDATE system_health_checks
                SET last_run_at = ?,
                    runs_today = ?,
                    last_status = 'ok',
                    last_error = NULL,
                    updated_at = ?
                WHERE service_name = ?
            ");
            $stmt->execute([$now, $runsToday, $now, $serviceName]);
        } else {
            // Créer un nouvel enregistrement
            $stmt = $db->prepare("
                INSERT INTO system_health_checks (service_name, last_run_at, runs_today, last_status, updated_at)
                VALUES (?, ?, 1, 'ok', ?)
            ");
            $stmt->execute([$serviceName, $now, $now]);
        }
    }

    /**
     * Enregistrer une erreur
     */
    public static function recordError(string $serviceName, string $error): void
    {
        $now = date('Y-m-d H:i:s');
        $db = self::getDb();

        $stmt = $db->prepare("
            UPDATE system_health_checks
            SET last_status = 'error',
                last_error = ?,
                updated_at = ?
            WHERE service_name = ?
        ");
        $stmt->execute([$error, $now, $serviceName]);
    }

    /**
     * Vérifier l'état de santé d'un service
     *
     * @return array ['status' => 'ok|warning|error', 'last_run' => timestamp, 'runs_today' => int]
     */
    public static function checkServiceHealth(string $serviceName): array
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM system_health_checks WHERE service_name = ?");
        $stmt->execute([$serviceName]);
        $service = $stmt->fetch();

        if (!$service) {
            return [
                'status' => 'error',
                'message' => 'Service non configuré',
                'last_run' => null,
                'runs_today' => 0
            ];
        }

        // Si jamais tourné
        if (!$service['last_run_at']) {
            return [
                'status' => 'warning',
                'message' => 'Le service n\'a jamais tourné',
                'last_run' => null,
                'runs_today' => 0
            ];
        }

        $lastRun = strtotime($service['last_run_at']);
        $now = time();
        $hoursSinceLastRun = ($now - $lastRun) / 3600;

        // Alerte si pas tourné depuis plus de 24h
        if ($hoursSinceLastRun > 24) {
            return [
                'status' => 'error',
                'message' => sprintf('Pas d\'activité depuis %d heures', round($hoursSinceLastRun)),
                'last_run' => $service['last_run_at'],
                'runs_today' => $service['runs_today'],
                'hours_since' => round($hoursSinceLastRun, 1)
            ];
        }

        // Warning si pas tourné depuis plus de 2h (pour les crons fréquents)
        if ($hoursSinceLastRun > 2) {
            return [
                'status' => 'warning',
                'message' => sprintf('Dernière activité il y a %d heures', round($hoursSinceLastRun)),
                'last_run' => $service['last_run_at'],
                'runs_today' => $service['runs_today'],
                'hours_since' => round($hoursSinceLastRun, 1)
            ];
        }

        // Tout va bien
        return [
            'status' => 'ok',
            'message' => 'Service actif',
            'last_run' => $service['last_run_at'],
            'runs_today' => $service['runs_today'],
            'hours_since' => round($hoursSinceLastRun, 1)
        ];
    }

    /**
     * Vérifier l'état de tous les services
     */
    public static function checkAllServices(): array
    {
        $db = self::getDb();
        $stmt = $db->query("SELECT * FROM system_health_checks");
        $services = $stmt->fetchAll();

        $results = [];
        $globalStatus = 'ok';

        foreach ($services as $service) {
            $health = self::checkServiceHealth($service['service_name']);
            $results[$service['service_name']] = $health;

            // Déterminer le statut global
            if ($health['status'] === 'error') {
                $globalStatus = 'error';
            } elseif ($health['status'] === 'warning' && $globalStatus !== 'error') {
                $globalStatus = 'warning';
            }
        }

        return [
            'global_status' => $globalStatus,
            'services' => $results,
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtenir un résumé simple (pour API)
     */
    public static function getHealthSummary(): array
    {
        $check = self::checkAllServices();

        return [
            'status' => $check['global_status'],
            'timestamp' => time(),
            'datetime' => $check['checked_at'],
            'services' => array_map(function($service) {
                return [
                    'status' => $service['status'],
                    'last_run' => $service['last_run'],
                    'runs_today' => $service['runs_today']
                ];
            }, $check['services'])
        ];
    }
}
