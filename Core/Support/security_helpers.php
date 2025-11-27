<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     * 
     * @param string|null $value
     * @return string
     */
    function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
    }
}

if (!function_exists('json_safe')) {
    /**
     * Safely encode value to JSON
     * 
     * @param mixed $value
     * @return string
     */
    function json_safe(mixed $value): string
    {
        $json = json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);

        if ($json === false) {
            return '{}';
        }

        return $json;
    }
}

if (!function_exists('sanitize_path')) {
    /**
     * Sanitize file path (prevent directory traversal)
     * 
     * @param string $path
     * @return string
     */
    function sanitize_path(string $path): string
    {
        // Remove any null bytes
        $path = str_replace("\0", '', $path);

        // Remove .. and convert to forward slashes
        $path = str_replace(['..', '\\'], ['', '/'], $path);

        // Remove multiple slashes
        $path = preg_replace('#/+#', '/', $path);

        return $path ?? '';
    }
}

if (!function_exists('hash_email')) {
    /**
     * Hash email for privacy (GDPR-friendly)
     * 
     * @param string $email
     * @return string
     */
    function hash_email(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }
}

if (!function_exists('rate_limit_key')) {
    /**
     * Generate rate limit key
     * 
     * @param string $identifier
     * @param string $action
     * @return string
     */
    function rate_limit_key(string $identifier, string $action = 'general'): string
    {
        return 'rate_limit:' . $action . ':' . hash('sha256', $identifier);
    }
}
