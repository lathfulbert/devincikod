<?php

namespace App\Core\Middleware;

use Modules\ApiKeys\Models\ApiKey;
use App\Core\Database\Database;

/**
 * Rate Limiting Middleware
 *
 * Implements token bucket algorithm for rate limiting
 * Limits requests per API key based on configurable rules
 */
class RateLimitMiddleware
{
    private Database $db;
    private array $config;

    // Default rate limits
    private const DEFAULT_REQUESTS_PER_MINUTE = 60;
    private const DEFAULT_REQUESTS_PER_HOUR = 1000;
    private const DEFAULT_REQUESTS_PER_DAY = 10000;

    public function __construct(array $config = [])
    {
        $this->db = Database::getInstance();
        $this->config = array_merge([
            'requests_per_minute' => self::DEFAULT_REQUESTS_PER_MINUTE,
            'requests_per_hour' => self::DEFAULT_REQUESTS_PER_HOUR,
            'requests_per_day' => self::DEFAULT_REQUESTS_PER_DAY,
            'enable_minute_limit' => true,
            'enable_hour_limit' => true,
            'enable_day_limit' => true,
        ], $config);
    }

    /**
     * Handle the middleware
     *
     * @return bool
     */
    public function handle(): bool
    {
        $apiKey = $_SERVER['API_KEY'] ?? null;

        if (!$apiKey || !($apiKey instanceof ApiKey)) {
            // No API key means no rate limiting (might be a regular web request)
            return true;
        }

        // Get rate limit configuration for this key
        $rateLimits = $this->getRateLimitsForKey($apiKey);

        // Check each rate limit
        if ($this->config['enable_minute_limit']) {
            if (!$this->checkRateLimit($apiKey->id, 'minute', $rateLimits['requests_per_minute'])) {
                $this->sendRateLimitResponse('minute', $rateLimits['requests_per_minute']);
                return false;
            }
        }

        if ($this->config['enable_hour_limit']) {
            if (!$this->checkRateLimit($apiKey->id, 'hour', $rateLimits['requests_per_hour'])) {
                $this->sendRateLimitResponse('hour', $rateLimits['requests_per_hour']);
                return false;
            }
        }

        if ($this->config['enable_day_limit']) {
            if (!$this->checkRateLimit($apiKey->id, 'day', $rateLimits['requests_per_day'])) {
                $this->sendRateLimitResponse('day', $rateLimits['requests_per_day']);
                return false;
            }
        }

        // All rate limit checks passed
        return true;
    }

    /**
     * Check if request is within rate limit
     *
     * @param int $apiKeyId
     * @param string $window ('minute', 'hour', 'day')
     * @param int $limit
     * @return bool
     */
    private function checkRateLimit(int $apiKeyId, string $window, int $limit): bool
    {
        $now = time();
        $cacheKey = "rate_limit:{$apiKeyId}:{$window}";

        // Get window duration in seconds
        $windowSeconds = $this->getWindowSeconds($window);
        $windowStart = $now - $windowSeconds;

        // Count requests in this window using database
        $sql = "SELECT COUNT(*) as count
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= FROM_UNIXTIME(?)";

        $stmt = $this->db->query($sql, [$apiKeyId, $windowStart]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $requestCount = $result['count'] ?? 0;

        // Check if limit exceeded
        return $requestCount < $limit;
    }

    /**
     * Get window duration in seconds
     */
    private function getWindowSeconds(string $window): int
    {
        switch ($window) {
            case 'minute':
                return 60;
            case 'hour':
                return 3600;
            case 'day':
                return 86400;
            default:
                return 3600;
        }
    }

    /**
     * Get rate limits for a specific API key
     * Can be customized per key using metadata
     */
    private function getRateLimitsForKey(ApiKey $apiKey): array
    {
        // Check if API key has custom rate limits in permissions JSON
        $permissions = json_decode($apiKey->permissions ?? '{}', true);

        return [
            'requests_per_minute' => $permissions['rate_limit_minute'] ?? $this->config['requests_per_minute'],
            'requests_per_hour' => $permissions['rate_limit_hour'] ?? $this->config['requests_per_hour'],
            'requests_per_day' => $permissions['rate_limit_day'] ?? $this->config['requests_per_day'],
        ];
    }

    /**
     * Send rate limit exceeded response
     */
    private function sendRateLimitResponse(string $window, int $limit): void
    {
        $windowSeconds = $this->getWindowSeconds($window);
        $retryAfter = $windowSeconds;

        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: ' . $retryAfter);
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Window: ' . $window);

        echo json_encode([
            'success' => false,
            'error' => 'Rate Limit Exceeded',
            'message' => "Too many requests. Limit: {$limit} requests per {$window}.",
            'retry_after' => $retryAfter,
            'limit' => $limit,
            'window' => $window
        ]);

        exit;
    }

    /**
     * Get remaining requests for an API key
     * Useful for X-RateLimit-Remaining header
     */
    public static function getRemainingRequests(int $apiKeyId, string $window, int $limit): int
    {
        $db = Database::getInstance();
        $now = time();

        $windowSeconds = match($window) {
            'minute' => 60,
            'hour' => 3600,
            'day' => 86400,
            default => 3600
        };

        $windowStart = $now - $windowSeconds;

        $sql = "SELECT COUNT(*) as count
                FROM api_request_logs
                WHERE api_key_id = ?
                AND requested_at >= FROM_UNIXTIME(?)";

        $stmt = $db->query($sql, [$apiKeyId, $windowStart]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $requestCount = $result['count'] ?? 0;

        return max(0, $limit - $requestCount);
    }

    /**
     * Invoke method for middleware usage
     */
    public function __invoke()
    {
        return $this->handle();
    }
}
