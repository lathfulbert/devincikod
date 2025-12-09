<?php

namespace App\Core\Services;

/**
 * Helper pour enregistrer facilement les heartbeats dans les cron jobs
 */
class HeartbeatHelper
{
    /**
     * Wrapper pour exécuter un cron job avec enregistrement automatique
     *
     * @param string $serviceName Nom du service (ex: 'cron_job', 'queue_worker')
     * @param callable $callback Fonction à exécuter
     * @return mixed Résultat de la fonction
     */
    public static function runWithHeartbeat(string $serviceName, callable $callback)
    {
        try {
            // Enregistrer le début
            HealthCheckService::recordHeartbeat($serviceName);

            // Exécuter la tâche
            $result = $callback();

            return $result;
        } catch (\Exception $e) {
            // Enregistrer l'erreur
            HealthCheckService::recordError($serviceName, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enregistrer simplement qu'un cron a tourné
     */
    public static function ping(string $serviceName): void
    {
        HealthCheckService::recordHeartbeat($serviceName);
    }
}
