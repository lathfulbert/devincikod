<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use App\Core\Services\HealthCheckService;

class HealthCheckController
{
    /**
     * Dashboard de monitoring
     */
    public function index()
    {
        $healthCheck = HealthCheckService::checkAllServices();

        echo view('Settings/health/index', [
            'healthCheck' => $healthCheck,
            'title' => 'System Health Monitor'
        ]);
    }

    /**
     * API endpoint pour vérification externe
     * Usage: curl https://yoursite.com/api/health
     */
    public function apiCheck()
    {
        header('Content-Type: application/json');

        $summary = HealthCheckService::getHealthSummary();

        // Code de statut HTTP selon l'état
        $httpCode = match($summary['status']) {
            'ok' => 200,
            'warning' => 200, // 200 mais avec avertissement
            'error' => 503,   // Service Unavailable
            default => 500
        };

        http_response_code($httpCode);
        echo json_encode($summary, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Badge SVG pour affichage externe
     * Usage: <img src="https://yoursite.com/api/health/badge">
     */
    public function badge()
    {
        $summary = HealthCheckService::getHealthSummary();

        $status = $summary['status'];
        $color = match($status) {
            'ok' => 'brightgreen',
            'warning' => 'yellow',
            'error' => 'red',
            default => 'lightgrey'
        };

        $label = match($status) {
            'ok' => 'healthy',
            'warning' => 'degraded',
            'error' => 'down',
            default => 'unknown'
        };

        // Rediriger vers shields.io pour générer le badge
        $url = "https://img.shields.io/badge/system-{$label}-{$color}";
        header("Location: {$url}");
        exit;
    }
}
