<?php

namespace Modules\ApiKeys\Controllers;

use App\Core\Application;
use Modules\ApiKeys\Models\ApiKey;
use Modules\ApiKeys\Services\ApiMonitoringService;

class ApiMonitoringController
{
    private ApiMonitoringService $monitoringService;

    public function __construct()
    {
        $this->monitoringService = new ApiMonitoringService();
    }

    /**
     * Display monitoring dashboard
     */
    public function index()
    {
        $app = Application::getInstance();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        // Get user's API keys
        $apiKeys = ApiKey::where('user_id', $userId)->get();

        // Get alerts for all keys
        $allAlerts = [];
        $healthStatuses = [];

        foreach ($apiKeys as $key) {
            $alerts = $this->monitoringService->needsAttention($key->id);
            if (!empty($alerts)) {
                $allAlerts[$key->id] = [
                    'key' => $key,
                    'alerts' => $alerts
                ];
            }

            $healthStatuses[$key->id] = $this->monitoringService->getHealthStatus($key->id, '1h');
        }

        echo $app->view->render('backend/apikeys/monitoring', [
            'title' => 'API Monitoring',
            'apiKeys' => $apiKeys,
            'allAlerts' => $allAlerts,
            'healthStatuses' => $healthStatuses
        ]);
    }

    /**
     * Get detailed monitoring data for a specific key
     */
    public function details()
    {
        $app = Application::getInstance();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $apiKeyId = $_GET['api_key_id'] ?? null;

        if (!$apiKeyId) {
            $_SESSION['flash_error'] = 'API key ID required';
            redirect('/admin/system-api-keys/monitoring');
            exit;
        }

        // Verify ownership
        $apiKey = ApiKey::where('id', $apiKeyId)
            ->where('user_id', $userId)
            ->first();

        if (!$apiKey) {
            $_SESSION['flash_error'] = 'API key not found';
            redirect('/admin/system-api-keys/monitoring');
            exit;
        }

        // Get comprehensive monitoring data
        $usageSummary = $this->monitoringService->getUsageSummary($apiKeyId);
        $anomalies = $this->monitoringService->detectAnomalies($apiKeyId);
        $alerts = $this->monitoringService->needsAttention($apiKeyId);

        echo $app->view->render('backend/apikeys/monitoring-details', [
            'title' => 'API Monitoring - ' . $apiKey->name,
            'apiKey' => $apiKey,
            'usageSummary' => $usageSummary,
            'anomalies' => $anomalies,
            'alerts' => $alerts
        ]);
    }

    /**
     * AJAX endpoint for real-time health status
     */
    public function healthStatus()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $apiKeyId = $_GET['api_key_id'] ?? null;

        if (!$apiKeyId) {
            echo json_encode(['success' => false, 'message' => 'API key ID required']);
            exit;
        }

        // Verify ownership
        $apiKey = ApiKey::where('id', $apiKeyId)
            ->where('user_id', $userId)
            ->first();

        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => 'API key not found']);
            exit;
        }

        $period = $_GET['period'] ?? '1h';
        $health = $this->monitoringService->getHealthStatus($apiKeyId, $period);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $health]);
        exit;
    }

    /**
     * AJAX endpoint for anomaly detection
     */
    public function anomalies()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $apiKeyId = $_GET['api_key_id'] ?? null;

        if (!$apiKeyId) {
            echo json_encode(['success' => false, 'message' => 'API key ID required']);
            exit;
        }

        // Verify ownership
        $apiKey = ApiKey::where('id', $apiKeyId)
            ->where('user_id', $userId)
            ->first();

        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => 'API key not found']);
            exit;
        }

        $anomalies = $this->monitoringService->detectAnomalies($apiKeyId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $anomalies]);
        exit;
    }
}
