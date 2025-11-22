<?php

namespace App\Core\Logging\Middleware;

class HttpRequestLogger
{
    public function handle($request, $next)
    {
        $startTime = microtime(true);

        // Process request
        $response = $next($request);

        // Calculate duration
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Log request details
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        $context = [
            'method' => $method,
            'uri' => $uri,
            'ip' => $ip,
            'duration' => $duration . 'ms',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];

        // Log level depends on duration or status code (if available)
        if ($duration > 1000) {
            logger()->warning("Slow request: {$method} {$uri}", $context);
        } else {
            logger()->info("HTTP Request: {$method} {$uri}", $context);
        }

        return $response;
    }
}
