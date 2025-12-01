<?php

namespace Modules\ApiKeys\Models;

use App\Core\Database\Model;

class ApiRequestLog extends Model
{
    protected static string $table = 'api_request_logs';

    protected $fillable = [
        'api_key_id',
        'user_id',
        'endpoint',
        'method',
        'ip_address',
        'request_headers',
        'request_body',
        'status_code',
        'response_body',
        'response_time',
        'user_agent',
        'referer',
        'requested_at'
    ];

    /**
     * Log an API request
     */
    public static function logRequest(array $data): self
    {
        $log = new self();
        $log->api_key_id = $data['api_key_id'] ?? null;
        $log->user_id = $data['user_id'] ?? null;
        $log->endpoint = $data['endpoint'] ?? '';
        $log->method = $data['method'] ?? 'GET';
        $log->ip_address = $data['ip_address'] ?? '';
        $log->request_headers = isset($data['request_headers']) ? json_encode($data['request_headers']) : null;
        $log->request_body = isset($data['request_body']) ? json_encode($data['request_body']) : null;
        $log->status_code = $data['status_code'] ?? 200;
        $log->response_body = isset($data['response_body']) ? json_encode($data['response_body']) : null;
        $log->response_time = $data['response_time'] ?? null;
        $log->user_agent = $data['user_agent'] ?? null;
        $log->referer = $data['referer'] ?? null;
        $log->requested_at = $data['requested_at'] ?? date('Y-m-d H:i:s');
        $log->save();

        return $log;
    }

    /**
     * Get logs for a specific API key
     */
    public static function getLogsForApiKey(int $apiKeyId, int $limit = 100): array
    {
        return self::where('api_key_id', $apiKeyId)
            ->orderBy('requested_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a specific user
     */
    public static function getLogsForUser(int $userId, int $limit = 100): array
    {
        return self::where('user_id', $userId)
            ->orderBy('requested_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics for an API key
     */
    public static function getStatsForApiKey(int $apiKeyId, string $period = '24h'): array
    {
        $db = \App\Core\Database\Database::getInstance();

        $dateCondition = self::getDateCondition($period);

        $sql = "SELECT
                    COUNT(*) as total_requests,
                    COUNT(DISTINCT endpoint) as unique_endpoints,
                    AVG(response_time) as avg_response_time,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count
                FROM api_request_logs
                WHERE api_key_id = ? AND requested_at >= {$dateCondition}";

        $stmt = $db->query($sql, [$apiKeyId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get request count by endpoint
     */
    public static function getEndpointStats(int $apiKeyId, string $period = '24h'): array
    {
        $db = \App\Core\Database\Database::getInstance();

        $dateCondition = self::getDateCondition($period);

        $sql = "SELECT
                    endpoint,
                    COUNT(*) as request_count,
                    AVG(response_time) as avg_response_time,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count
                FROM api_request_logs
                WHERE api_key_id = ? AND requested_at >= {$dateCondition}
                GROUP BY endpoint
                ORDER BY request_count DESC";

        $stmt = $db->query($sql, [$apiKeyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get request count over time (for charts)
     */
    public static function getRequestTimeSeries(int $apiKeyId, string $period = '24h', string $interval = '1h'): array
    {
        $db = \App\Core\Database\Database::getInstance();

        $dateCondition = self::getDateCondition($period);
        $groupBy = self::getTimeGrouping($interval);

        $sql = "SELECT
                    {$groupBy} as time_bucket,
                    COUNT(*) as request_count,
                    AVG(response_time) as avg_response_time
                FROM api_request_logs
                WHERE api_key_id = ? AND requested_at >= {$dateCondition}
                GROUP BY time_bucket
                ORDER BY time_bucket";

        $stmt = $db->query($sql, [$apiKeyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get status code distribution
     */
    public static function getStatusCodeDistribution(int $apiKeyId, string $period = '24h'): array
    {
        $db = \App\Core\Database\Database::getInstance();

        $dateCondition = self::getDateCondition($period);

        $sql = "SELECT
                    status_code,
                    COUNT(*) as count
                FROM api_request_logs
                WHERE api_key_id = ? AND requested_at >= {$dateCondition}
                GROUP BY status_code
                ORDER BY count DESC";

        $stmt = $db->query($sql, [$apiKeyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Clean old logs
     */
    public static function cleanOldLogs(int $daysToKeep = 30): int
    {
        $db = \App\Core\Database\Database::getInstance();

        $sql = "DELETE FROM api_request_logs WHERE requested_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $db->query($sql, [$daysToKeep]);

        return $stmt->rowCount();
    }

    /**
     * Get date condition for SQL queries
     */
    private static function getDateCondition(string $period): string
    {
        switch ($period) {
            case '1h':
                return "DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            case '24h':
                return "DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            case '7d':
                return "DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30d':
                return "DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90d':
                return "DATE_SUB(NOW(), INTERVAL 90 DAY)";
            default:
                return "DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        }
    }

    /**
     * Get time grouping for SQL queries
     */
    private static function getTimeGrouping(string $interval): string
    {
        switch ($interval) {
            case '5m':
                return "DATE_FORMAT(requested_at, '%Y-%m-%d %H:%i:00')";
            case '15m':
                return "DATE_FORMAT(requested_at, '%Y-%m-%d %H:%i:00')";
            case '1h':
                return "DATE_FORMAT(requested_at, '%Y-%m-%d %H:00:00')";
            case '1d':
                return "DATE_FORMAT(requested_at, '%Y-%m-%d')";
            default:
                return "DATE_FORMAT(requested_at, '%Y-%m-%d %H:00:00')";
        }
    }

    /**
     * Relationship: belongs to ApiKey
     */
    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class, 'api_key_id');
    }

    /**
     * Relationship: belongs to User
     */
    public function user()
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'user_id');
    }
}
