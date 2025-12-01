<?php

namespace Modules\ApiKeys\Controllers;

use App\Core\Application;
use Modules\ApiKeys\Models\ApiKey;
use Modules\ApiKeys\Models\ApiRequestLog;

class ApiAnalyticsController
{
    /**
     * Display API analytics dashboard
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

        // Get selected API key (default to first one)
        $selectedKeyId = $_GET['api_key_id'] ?? ($apiKeys[0]->id ?? null);
        $period = $_GET['period'] ?? '24h';

        $stats = null;
        $endpointStats = [];
        $timeSeries = [];
        $statusDistribution = [];
        $recentLogs = [];

        if ($selectedKeyId) {
            // Get overall statistics
            $stats = ApiRequestLog::getStatsForApiKey($selectedKeyId, $period);

            // Get endpoint statistics
            $endpointStats = ApiRequestLog::getEndpointStats($selectedKeyId, $period);

            // Get time series data for charts
            $interval = $this->getIntervalForPeriod($period);
            $timeSeries = ApiRequestLog::getRequestTimeSeries($selectedKeyId, $period, $interval);

            // Get status code distribution
            $statusDistribution = ApiRequestLog::getStatusCodeDistribution($selectedKeyId, $period);

            // Get recent logs
            $recentLogs = ApiRequestLog::getLogsForApiKey($selectedKeyId, 50);
        }

        echo $app->view->render('backend/apikeys/analytics', [
            'title' => 'API Analytics',
            'apiKeys' => $apiKeys,
            'selectedKeyId' => $selectedKeyId,
            'period' => $period,
            'stats' => $stats,
            'endpointStats' => $endpointStats,
            'timeSeries' => $timeSeries,
            'statusDistribution' => $statusDistribution,
            'recentLogs' => $recentLogs
        ]);
    }

    /**
     * Get data for charts (AJAX endpoint)
     */
    public function chartData()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $apiKeyId = $_GET['api_key_id'] ?? null;
        $period = $_GET['period'] ?? '24h';
        $chartType = $_GET['chart_type'] ?? 'timeseries';

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

        $data = [];

        switch ($chartType) {
            case 'timeseries':
                $interval = $this->getIntervalForPeriod($period);
                $data = ApiRequestLog::getRequestTimeSeries($apiKeyId, $period, $interval);
                break;

            case 'endpoints':
                $data = ApiRequestLog::getEndpointStats($apiKeyId, $period);
                break;

            case 'status':
                $data = ApiRequestLog::getStatusCodeDistribution($apiKeyId, $period);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid chart type']);
                exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    /**
     * Export analytics data
     */
    public function export()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $apiKeyId = $_GET['api_key_id'] ?? null;
        $period = $_GET['period'] ?? '24h';
        $format = $_GET['format'] ?? 'csv';

        if (!$apiKeyId) {
            $_SESSION['flash_error'] = 'API key ID required';
            redirect('/admin/system-api-keys/analytics');
            exit;
        }

        // Verify ownership
        $apiKey = ApiKey::where('id', $apiKeyId)
            ->where('user_id', $userId)
            ->first();

        if (!$apiKey) {
            $_SESSION['flash_error'] = 'API key not found';
            redirect('/admin/system-api-keys/analytics');
            exit;
        }

        $logs = ApiRequestLog::getLogsForApiKey($apiKeyId, 10000);

        if ($format === 'csv') {
            $this->exportCsv($logs, $apiKey->name);
        } elseif ($format === 'json') {
            $this->exportJson($logs, $apiKey->name);
        }

        exit;
    }

    /**
     * Get appropriate interval based on period
     */
    private function getIntervalForPeriod(string $period): string
    {
        switch ($period) {
            case '1h':
                return '5m';
            case '24h':
                return '1h';
            case '7d':
                return '1d';
            case '30d':
                return '1d';
            case '90d':
                return '1d';
            default:
                return '1h';
        }
    }

    /**
     * Export logs as CSV
     */
    private function exportCsv(array $logs, string $keyName): void
    {
        $filename = 'api-logs-' . $keyName . '-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV header
        fputcsv($output, [
            'ID',
            'Endpoint',
            'Method',
            'Status Code',
            'IP Address',
            'Response Time (ms)',
            'Requested At'
        ]);

        // CSV data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->id,
                $log->endpoint,
                $log->method,
                $log->status_code,
                $log->ip_address,
                $log->response_time,
                $log->requested_at
            ]);
        }

        fclose($output);
    }

    /**
     * Export logs as JSON
     */
    private function exportJson(array $logs, string $keyName): void
    {
        $filename = 'api-logs-' . $keyName . '-' . date('Y-m-d-His') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode($logs, JSON_PRETTY_PRINT);
    }
}
