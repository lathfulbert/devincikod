<?php

namespace Modules\ApiKeys\Services;

use Modules\ApiKeys\Models\ApiKey;
use Modules\ApiKeys\Models\ApiRequestLog;
use App\Core\Database\Database;

/**
 * API Monitoring Service
 *
 * Provides monitoring and alerting functionality for API usage
 */
class ApiMonitoringService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get health status for an API key
     */
    public function getHealthStatus(int $apiKeyId, string $period = '1h'): array
    {
        $stats = ApiRequestLog::getStatsForApiKey($apiKeyId, $period);

        $totalRequests = $stats['total_requests'] ?? 0;
        $errorCount = $stats['error_count'] ?? 0;
        $avgResponseTime = $stats['avg_response_time'] ?? 0;

        // Calculate error rate
        $errorRate = $totalRequests > 0 ? ($errorCount / $totalRequests) * 100 : 0;

        // Determine health status
        $status = 'healthy';
        $issues = [];

        if ($errorRate > 10) {
            $status = 'critical';
            $issues[] = "High error rate: {$errorRate}%";
        } elseif ($errorRate > 5) {
            $status = 'warning';
            $issues[] = "Elevated error rate: {$errorRate}%";
        }

        if ($avgResponseTime > 1000) {
            $status = $status === 'critical' ? 'critical' : 'warning';
            $issues[] = "Slow response time: {$avgResponseTime}ms";
        }

        return [
            'status' => $status,
            'error_rate' => $errorRate,
            'avg_response_time' => $avgResponseTime,
            'total_requests' => $totalRequests,
            'issues' => $issues
        ];
    }

    /**
     * Get anomaly detection results
     */
    public function detectAnomalies(int $apiKeyId): array
    {
        $anomalies = [];

        // Check for sudden spike in errors
        $recentErrors = $this->getRecentErrors($apiKeyId, 60); // Last hour
        $historicalErrors = $this->getHistoricalErrorRate($apiKeyId);

        if ($recentErrors > $historicalErrors * 3) {
            $anomalies[] = [
                'type' => 'error_spike',
                'severity' => 'high',
                'message' => 'Unusual spike in error rate detected',
                'current' => $recentErrors,
                'historical' => $historicalErrors
            ];
        }

        // Check for sudden traffic spike
        $recentTraffic = $this->getRecentTraffic($apiKeyId, 60);
        $historicalTraffic = $this->getHistoricalTraffic($apiKeyId);

        if ($recentTraffic > $historicalTraffic * 5) {
            $anomalies[] = [
                'type' => 'traffic_spike',
                'severity' => 'medium',
                'message' => 'Unusual spike in traffic detected',
                'current' => $recentTraffic,
                'historical' => $historicalTraffic
            ];
        }

        // Check for slow endpoints
        $slowEndpoints = $this->getSlowEndpoints($apiKeyId);
        if (!empty($slowEndpoints)) {
            $anomalies[] = [
                'type' => 'slow_endpoints',
                'severity' => 'medium',
                'message' => 'Some endpoints are responding slowly',
                'endpoints' => $slowEndpoints
            ];
        }

        return $anomalies;
    }

    /**
     * Get API key usage summary
     */
    public function getUsageSummary(int $apiKeyId): array
    {
        $stats24h = ApiRequestLog::getStatsForApiKey($apiKeyId, '24h');
        $stats7d = ApiRequestLog::getStatsForApiKey($apiKeyId, '7d');
        $stats30d = ApiRequestLog::getStatsForApiKey($apiKeyId, '30d');

        $topEndpoints = $this->getTopEndpoints($apiKeyId, 5);
        $topErrors = $this->getTopErrors($apiKeyId, 5);

        return [
            'last_24h' => $stats24h,
            'last_7d' => $stats7d,
            'last_30d' => $stats30d,
            'top_endpoints' => $topEndpoints,
            'top_errors' => $topErrors,
            'health_status' => $this->getHealthStatus($apiKeyId, '1h')
        ];
    }

    /**
     * Check if API key needs attention
     */
    public function needsAttention(int $apiKeyId): array
    {
        $alerts = [];

        // Check if key is expiring soon
        $apiKey = ApiKey::find($apiKeyId);
        if ($apiKey && $apiKey->expires_at) {
            $daysUntilExpiry = (strtotime($apiKey->expires_at) - time()) / 86400;
            if ($daysUntilExpiry > 0 && $daysUntilExpiry <= 7) {
                $alerts[] = [
                    'type' => 'expiring_soon',
                    'severity' => 'warning',
                    'message' => "API key expires in {$daysUntilExpiry} days",
                    'expires_at' => $apiKey->expires_at
                ];
            }
        }

        // Check health status
        $health = $this->getHealthStatus($apiKeyId, '1h');
        if ($health['status'] !== 'healthy') {
            $alerts[] = [
                'type' => 'health_issue',
                'severity' => $health['status'] === 'critical' ? 'high' : 'medium',
                'message' => 'API health issues detected',
                'issues' => $health['issues']
            ];
        }

        // Check for anomalies
        $anomalies = $this->detectAnomalies($apiKeyId);
        foreach ($anomalies as $anomaly) {
            $alerts[] = $anomaly;
        }

        return $alerts;
    }

    /**
     * Get recent error rate (last N minutes)
     */
    private function getRecentErrors(int $apiKeyId, int $minutes): float
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";

        $stmt = $this->db->query($sql, [$apiKeyId, $minutes]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $total = $result['total'] ?? 0;
        $errors = $result['errors'] ?? 0;

        return $total > 0 ? ($errors / $total) * 100 : 0;
    }

    /**
     * Get historical error rate (average over last 7 days, excluding last hour)
     */
    private function getHistoricalErrorRate(int $apiKeyId): float
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND requested_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        $stmt = $this->db->query($sql, [$apiKeyId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $total = $result['total'] ?? 0;
        $errors = $result['errors'] ?? 0;

        return $total > 0 ? ($errors / $total) * 100 : 0;
    }

    /**
     * Get recent traffic count
     */
    private function getRecentTraffic(int $apiKeyId, int $minutes): int
    {
        $sql = "SELECT COUNT(*) as count
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";

        $stmt = $this->db->query($sql, [$apiKeyId, $minutes]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] ?? 0;
    }

    /**
     * Get historical traffic average
     */
    private function getHistoricalTraffic(int $apiKeyId): float
    {
        $sql = "SELECT COUNT(*) / 168 as avg_per_hour
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND requested_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        $stmt = $this->db->query($sql, [$apiKeyId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['avg_per_hour'] ?? 0;
    }

    /**
     * Get slow endpoints (avg response time > 1000ms)
     */
    private function getSlowEndpoints(int $apiKeyId): array
    {
        $sql = "SELECT
                    endpoint,
                    AVG(response_time) as avg_response_time,
                    COUNT(*) as request_count
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY endpoint
                HAVING avg_response_time > 1000
                ORDER BY avg_response_time DESC
                LIMIT 5";

        $stmt = $this->db->query($sql, [$apiKeyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get top endpoints by request count
     */
    private function getTopEndpoints(int $apiKeyId, int $limit = 5): array
    {
        $sql = "SELECT
                    endpoint,
                    COUNT(*) as request_count
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY endpoint
                ORDER BY request_count DESC
                LIMIT ?";

        $stmt = $this->db->query($sql, [$apiKeyId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get top errors by count
     */
    private function getTopErrors(int $apiKeyId, int $limit = 5): array
    {
        $sql = "SELECT
                    endpoint,
                    status_code,
                    COUNT(*) as error_count
                FROM api_request_logs
                WHERE api_key_id = ?
                AND status_code >= 400
                AND requested_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY endpoint, status_code
                ORDER BY error_count DESC
                LIMIT ?";

        $stmt = $this->db->query($sql, [$apiKeyId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Clean up old logs (keep only last N days)
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        return ApiRequestLog::cleanOldLogs($daysToKeep);
    }

    /**
     * Generate daily summary report for an API key
     */
    public function generateDailySummary(int $apiKeyId, string $date = null): array
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $sql = "SELECT
                    COUNT(*) as total_requests,
                    COUNT(DISTINCT endpoint) as unique_endpoints,
                    AVG(response_time) as avg_response_time,
                    MIN(response_time) as min_response_time,
                    MAX(response_time) as max_response_time,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status_code >= 400 AND status_code < 500 THEN 1 ELSE 0 END) as client_errors,
                    SUM(CASE WHEN status_code >= 500 THEN 1 ELSE 0 END) as server_errors
                FROM api_request_logs
                WHERE api_key_id = ?
                AND DATE(requested_at) = ?";

        $stmt = $this->db->query($sql, [$apiKeyId, $date]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: [];
    }
}
