# API Ecosystem Documentation

## Overview

SunuFramework2 provides a comprehensive API ecosystem with authentication, monitoring, analytics, and rate limiting capabilities. This document covers the complete API management system.

## Table of Contents

1. [API Key Management](#api-key-management)
2. [API Authentication](#api-authentication)
3. [Rate Limiting](#rate-limiting)
4. [Request Logging](#request-logging)
5. [Analytics Dashboard](#analytics-dashboard)
6. [Monitoring & Health](#monitoring--health)
7. [Best Practices](#best-practices)

---

## API Key Management

### Creating API Keys

API keys can be created through the admin interface at `/admin/system-api-keys`:

```php
// Programmatically create an API key
use Modules\ApiKeys\Models\ApiKey;

$apiKey = new ApiKey();
$apiKey->user_id = $userId;
$apiKey->name = 'Mobile App - Production';
$apiKey->key = ApiKey::generateKey('sk_live'); // Generates secure key
$apiKey->prefix = 'sk_live';
$apiKey->ip_whitelist = '192.168.1.1,10.0.0.1'; // Optional
$apiKey->expires_at = date('Y-m-d H:i:s', strtotime('+1 year')); // Optional
$apiKey->is_active = 1;
$apiKey->save();
```

### Key Prefixes

- `sk_live` - Production keys
- `sk_test` - Testing/staging keys
- `sk_dev` - Development keys

### Key Properties

- **Name**: Descriptive name for identification
- **Prefix**: Environment identifier
- **Permissions**: JSON array of allowed scopes
- **IP Whitelist**: Comma-separated list of allowed IPs
- **Expires At**: Optional expiration date
- **Is Active**: Enable/disable the key

### Revoking Keys

```php
$apiKey = ApiKey::find($keyId);
$apiKey->is_active = 0;
$apiKey->save();
```

---

## API Authentication

### Using the API Key Middleware

The `ApiKeyMiddleware` handles API authentication using Bearer tokens:

```php
use App\Core\Middleware\ApiKeyMiddleware;

// In your route definition
['GET', '/api/users', [UserApiController::class, 'index'], [
    [new ApiKeyMiddleware(), 'handle']
]],

// With permission check
['POST', '/api/users', [UserApiController::class, 'store'], [
    [new ApiKeyMiddleware('users.create'), 'handle']
]],
```

### Making Authenticated Requests

#### Using Authorization Header (Recommended)

```bash
curl -H "Authorization: Bearer sk_live_abcd1234..." \
     https://yourapp.com/api/users
```

#### Using X-API-Key Header

```bash
curl -H "X-API-Key: sk_live_abcd1234..." \
     https://yourapp.com/api/users
```

#### Using Query Parameter (Not recommended for production)

```bash
curl https://yourapp.com/api/users?api_key=sk_live_abcd1234...
```

### Permission-Based Access

API keys can have specific permissions defined in JSON format:

```php
$apiKey->setPermissionsArray(['users.read', 'posts.read', 'posts.create']);
```

Then protect routes with specific permissions:

```php
new ApiKeyMiddleware('users.create') // Requires 'users.create' permission
```

### Security Features

1. **IP Whitelisting**: Restrict keys to specific IP addresses
2. **Expiration Dates**: Set automatic expiry for temporary keys
3. **Permission Scopes**: Fine-grained access control
4. **Key Revocation**: Instantly disable compromised keys

---

## Rate Limiting

### Configuration

The `RateLimitMiddleware` implements token bucket algorithm with configurable limits:

```php
use App\Core\Middleware\RateLimitMiddleware;

$rateLimiter = new RateLimitMiddleware([
    'requests_per_minute' => 60,
    'requests_per_hour' => 1000,
    'requests_per_day' => 10000,
    'enable_minute_limit' => true,
    'enable_hour_limit' => true,
    'enable_day_limit' => true,
]);
```

### Applying Rate Limits to Routes

```php
['GET', '/api/users', [UserApiController::class, 'index'], [
    [new ApiKeyMiddleware(), 'handle'],
    [new RateLimitMiddleware(), 'handle']
]],
```

### Per-Key Rate Limits

Set custom rate limits for specific API keys using the permissions field:

```php
$apiKey->permissions = json_encode([
    'rate_limit_minute' => 120,  // 120 requests per minute
    'rate_limit_hour' => 5000,   // 5000 requests per hour
    'rate_limit_day' => 50000    // 50000 requests per day
]);
$apiKey->save();
```

### Rate Limit Response

When rate limit is exceeded, the API returns a 429 status code:

```json
{
    "success": false,
    "error": "Rate Limit Exceeded",
    "message": "Too many requests. Limit: 60 requests per minute.",
    "retry_after": 60,
    "limit": 60,
    "window": "minute"
}
```

Response headers:
- `Retry-After`: Seconds until limit resets
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Window`: Time window (minute/hour/day)

### Checking Remaining Requests

```php
use App\Core\Middleware\RateLimitMiddleware;

$remaining = RateLimitMiddleware::getRemainingRequests($apiKeyId, 'minute', 60);
```

---

## Request Logging

### Automatic Logging

All API requests passing through `ApiKeyMiddleware` are automatically logged to the `api_request_logs` table.

### Logged Data

- API key and user ID
- Endpoint and HTTP method
- Request headers and body
- Response status code and body
- Response time in milliseconds
- IP address, user agent, referer
- Timestamp

### Programmatic Logging

```php
use Modules\ApiKeys\Models\ApiRequestLog;

ApiRequestLog::logRequest([
    'api_key_id' => $apiKeyId,
    'user_id' => $userId,
    'endpoint' => '/api/users',
    'method' => 'GET',
    'ip_address' => '192.168.1.1',
    'status_code' => 200,
    'response_time' => 150,
    'requested_at' => date('Y-m-d H:i:s')
]);
```

### Querying Logs

```php
// Get logs for an API key
$logs = ApiRequestLog::getLogsForApiKey($apiKeyId, 100);

// Get statistics
$stats = ApiRequestLog::getStatsForApiKey($apiKeyId, '24h');
// Returns: total_requests, unique_endpoints, avg_response_time, success_count, error_count

// Get endpoint statistics
$endpointStats = ApiRequestLog::getEndpointStats($apiKeyId, '24h');

// Get time series data
$timeSeries = ApiRequestLog::getRequestTimeSeries($apiKeyId, '24h', '1h');

// Get status code distribution
$distribution = ApiRequestLog::getStatusCodeDistribution($apiKeyId, '24h');
```

### Log Cleanup

Automatically clean old logs to manage database size:

```php
// Keep only last 90 days
$deletedCount = ApiRequestLog::cleanOldLogs(90);
```

---

## Analytics Dashboard

### Accessing Analytics

Navigate to `/admin/system-api-keys/analytics` to view comprehensive API analytics.

### Available Metrics

#### Overview Cards
- **Total Requests**: Request count for selected period
- **Success Rate**: Percentage of successful requests (2xx status codes)
- **Average Response Time**: Mean response time in milliseconds
- **Error Count**: Number of failed requests (4xx/5xx status codes)

#### Time Series Chart
- Visualize request volume over time
- Configurable intervals: 5m, 15m, 1h, 1d

#### Status Code Distribution
- Pie chart showing response code breakdown
- Identifies most common status codes

#### Endpoint Statistics Table
- Requests per endpoint
- Average response time per endpoint
- Success/error counts per endpoint
- Success rate percentage

#### Recent Requests Log
- Real-time view of latest API calls
- Filterable and sortable

### Exporting Data

Export analytics data in CSV or JSON format:

```php
// CSV export
/admin/system-api-keys/analytics/export?api_key_id=123&period=7d&format=csv

// JSON export
/admin/system-api-keys/analytics/export?api_key_id=123&period=30d&format=json
```

### Programmatic Access

```php
use Modules\ApiKeys\Controllers\ApiAnalyticsController;

$controller = new ApiAnalyticsController();

// Get chart data via AJAX
$data = $controller->chartData();
```

---

## Monitoring & Health

### Health Status Monitoring

Access real-time health monitoring at `/admin/system-api-keys/monitoring`.

### Health Statuses

1. **Healthy**: Normal operation (error rate < 5%, response time < 1000ms)
2. **Warning**: Elevated issues (error rate 5-10% or response time > 1000ms)
3. **Critical**: Severe issues (error rate > 10%)

### Getting Health Status

```php
use Modules\ApiKeys\Services\ApiMonitoringService;

$monitor = new ApiMonitoringService();
$health = $monitor->getHealthStatus($apiKeyId, '1h');

// Returns:
// [
//     'status' => 'healthy|warning|critical',
//     'error_rate' => 2.5,
//     'avg_response_time' => 250,
//     'total_requests' => 1500,
//     'issues' => ['High error rate: 12%']
// ]
```

### Anomaly Detection

Automatically detects unusual patterns:

```php
$anomalies = $monitor->detectAnomalies($apiKeyId);

// Detects:
// - Sudden spike in errors (3x historical average)
// - Unusual traffic spike (5x historical average)
// - Slow endpoints (response time > 1000ms)
```

### Usage Summary

Get comprehensive overview:

```php
$summary = $monitor->getUsageSummary($apiKeyId);

// Returns:
// [
//     'last_24h' => [...],
//     'last_7d' => [...],
//     'last_30d' => [...],
//     'top_endpoints' => [...],
//     'top_errors' => [...],
//     'health_status' => [...]
// ]
```

### Alerts & Notifications

Check if an API key needs attention:

```php
$alerts = $monitor->needsAttention($apiKeyId);

// Returns array of alerts:
// [
//     ['type' => 'expiring_soon', 'severity' => 'warning', ...],
//     ['type' => 'health_issue', 'severity' => 'high', ...],
//     ['type' => 'error_spike', 'severity' => 'high', ...]
// ]
```

### Daily Summary Reports

Generate daily performance reports:

```php
$report = $monitor->generateDailySummary($apiKeyId, '2025-12-01');

// Returns:
// [
//     'total_requests' => 15000,
//     'unique_endpoints' => 25,
//     'avg_response_time' => 250,
//     'min_response_time' => 50,
//     'max_response_time' => 2500,
//     'success_count' => 14500,
//     'client_errors' => 400,
//     'server_errors' => 100
// ]
```

---

## Best Practices

### 1. Key Management

✅ **DO:**
- Use descriptive names for API keys
- Set expiration dates for temporary keys
- Use different keys for different environments
- Rotate keys regularly (every 3-6 months)
- Store keys securely (environment variables, vault)

❌ **DON'T:**
- Commit API keys to version control
- Share keys across multiple applications
- Use production keys in development
- Hard-code keys in your application

### 2. Security

✅ **DO:**
- Always use HTTPS for API requests
- Implement IP whitelisting for production keys
- Use Bearer token authentication (not query params)
- Monitor for suspicious activity
- Revoke compromised keys immediately

❌ **DON'T:**
- Pass keys in query parameters for sensitive operations
- Log full API keys in application logs
- Share keys via insecure channels (email, chat)

### 3. Rate Limiting

✅ **DO:**
- Set appropriate limits based on your infrastructure
- Implement retry logic with exponential backoff
- Monitor rate limit usage
- Use different limits for different tiers/users

❌ **DON'T:**
- Set limits too low (frustrates users)
- Set limits too high (risks overload)
- Ignore rate limit responses

### 4. Monitoring

✅ **DO:**
- Review analytics dashboard regularly
- Set up alerts for critical issues
- Monitor error rates and response times
- Track API usage trends
- Clean up old logs periodically

❌ **DON'T:**
- Ignore warning signals
- Let logs grow indefinitely
- Overlook anomaly detection alerts

### 5. Error Handling

✅ **DO:**
- Return meaningful error messages
- Use appropriate HTTP status codes
- Include error codes for client handling
- Log errors for debugging

❌ **DON'T:**
- Expose sensitive information in errors
- Return generic "Internal Server Error" messages
- Ignore client errors (4xx)

---

## API Response Format

### Success Response

```json
{
    "success": true,
    "data": {
        "users": [...]
    },
    "meta": {
        "total": 100,
        "page": 1,
        "per_page": 20
    }
}
```

### Error Response

```json
{
    "success": false,
    "error": "Unauthorized",
    "message": "Invalid API key",
    "code": "AUTH_INVALID_KEY"
}
```

---

## Migration Guide

### Running Migrations

The API ecosystem requires two migrations:

```bash
# Create api_keys table
php artisan migrate Modules\\ApiKeys\\Database\\Migrations\\CreateApiKeysTable

# Create api_request_logs table
php artisan migrate Modules\\ApiKeys\\Database\\Migrations\\CreateApiRequestLogsTable
```

### Database Schema

#### api_keys Table

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Owner user ID |
| name | VARCHAR(100) | Key description |
| key | VARCHAR(64) | API key (unique) |
| prefix | VARCHAR(20) | Key prefix |
| permissions | TEXT | JSON permissions |
| ip_whitelist | VARCHAR(500) | Allowed IPs |
| last_used_at | DATETIME | Last usage time |
| expires_at | DATETIME | Expiration date |
| is_active | BOOLEAN | Active status |
| created_at | TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | Update time |

#### api_request_logs Table

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| api_key_id | BIGINT | FK to api_keys |
| user_id | BIGINT | FK to users |
| endpoint | VARCHAR(255) | Request endpoint |
| method | VARCHAR(10) | HTTP method |
| ip_address | VARCHAR(45) | Client IP |
| request_headers | TEXT | JSON headers |
| request_body | TEXT | JSON body |
| status_code | INT | HTTP status |
| response_body | TEXT | JSON response |
| response_time | INT | Time in ms |
| user_agent | VARCHAR(500) | Client agent |
| referer | VARCHAR(500) | Referer URL |
| requested_at | DATETIME | Request time |

---

## Support & Troubleshooting

### Common Issues

**Issue**: API key not working
- Verify key is active (`is_active = 1`)
- Check expiration date
- Verify IP whitelist settings
- Ensure correct Authorization header format

**Issue**: Rate limit errors
- Check current usage in analytics
- Review rate limit configuration
- Implement exponential backoff
- Consider upgrading limits

**Issue**: Slow response times
- Review endpoint statistics
- Check database indexes
- Monitor server resources
- Consider caching strategies

---

## Additional Resources

- [ORM Documentation](./ORM.md)
- [Module Development](./MODULE_DEVELOPMENT.md)
- [API Implementation Guide](./Implementation%20API%20management.md)

---

## Version History

- **v1.0.0** (2025-12-01): Initial release
  - API key management
  - Request logging
  - Analytics dashboard
  - Monitoring & health checks
  - Rate limiting
