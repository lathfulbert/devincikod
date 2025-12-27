<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Security Headers Middleware
 * 
 * Adds security-related HTTP headers to all responses
 * Following OWASP recommendations and modern security best practices
 */
class SecurityHeaders
{
    /**
     * Apply security headers to the response
     */
    public static function apply(): void
    {
        // Prevent clickjacking attacks
        header('X-Frame-Options: DENY');

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable XSS protection (legacy browsers)
        header('X-XSS-Protection: 1; mode=block');

        // Control referrer information
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy (strict by default)
        // TODO: Customize based on application needs
        // Initializing default CSP
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tiny.cloud https://cdnjs.cloudflare.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tiny.cloud https://cdnjs.cloudflare.com; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com;";

        header("Content-Security-Policy: " . $csp);

        // Permissions Policy (disable sensitive features by default)
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');

        // HSTS (HTTP Strict Transport Security) - Enable in production with HTTPS
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Remove server signature
        header_remove('X-Powered-By');

        // Prevent caching of sensitive data
        if (self::isSensitiveRoute()) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }

    /**
     * Check if connection is HTTPS
     */
    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    /**
     * Check if current route is sensitive (admin, api, auth)
     */
    private static function isSensitiveRoute(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $sensitivePatterns = ['/admin', '/api', '/login', '/register', '/password'];

        foreach ($sensitivePatterns as $pattern) {
            if (str_contains($uri, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set custom CSP for specific routes
     */
    public static function setCustomCSP(string $policy): void
    {
        header("Content-Security-Policy: {$policy}");
    }
}
