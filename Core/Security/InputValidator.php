<?php

declare(strict_types=1);

namespace App\Core\Security;

/**
 * Input Validator
 * 
 * Provides strict validation and sanitization for user inputs
 * Prevents XSS, SQL Injection, and other input-based attacks
 */
class InputValidator
{
    /**
     * Validate and sanitize string input
     */
    public static function string(mixed $input, int $maxLength = 255): string
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException('Input must be a string');
        }

        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Trim and limit length
        $input = mb_substr(trim($input), 0, $maxLength);

        return $input;
    }

    /**
     * Validate and sanitize email
     */
    public static function email(mixed $input): string
    {
        $email = self::string($input, 320);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        return strtolower($email);
    }

    /**
     * Validate integer
     */
    public static function int(mixed $input, ?int $min = null, ?int $max = null): int
    {
        if (!is_numeric($input) && !is_int($input)) {
            throw new \InvalidArgumentException('Input must be numeric');
        }

        $value = (int)$input;

        if ($min !== null && $value < $min) {
            throw new \InvalidArgumentException("Value must be >= {$min}");
        }

        if ($max !== null && $value > $max) {
            throw new \InvalidArgumentException("Value must be <= {$max}");
        }

        return $value;
    }

    /**
     * Validate boolean
     */
    public static function bool(mixed $input): bool
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Validate URL
     */
    public static function url(mixed $input): string
    {
        $url = self::string($input, 2048);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        // Only allow http and https
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException('URL must use http or https');
        }

        return $url;
    }

    /**
     * Sanitize HTML (remove dangerous tags and attributes)
     */
    public static function html(mixed $input, array $allowedTags = []): string
    {
        $input = self::string($input, 65535);

        // Default safe tags
        if (empty($allowedTags)) {
            $allowedTags = ['p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li'];
        }

        // Use strip_tags for basic sanitization
        $allowed = '<' . implode('><', $allowedTags) . '>';
        $cleaned = strip_tags($input, $allowed);

        // Remove javascript: and data: URLs from href/src
        $cleaned = preg_replace('/href\s*=\s*["\']?\s*javascript:/i', 'href="#', $cleaned);
        $cleaned = preg_replace('/src\s*=\s*["\']?\s*data:/i', 'src="#', $cleaned);
        $cleaned = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);

        return $cleaned ?? '';
    }

    /**
     * Validate filename (prevent path traversal)
     */
    public static function filename(mixed $input): string
    {
        $filename = self::string($input, 255);

        // Remove path separators
        $filename = str_replace(['/', '\\', '..'], '', $filename);

        // Remove null bytes and control characters
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);

        if (empty($filename)) {
            throw new \InvalidArgumentException('Invalid filename');
        }

        return $filename;
    }

    /**
     * Validate date
     */
    public static function date(mixed $input, string $format = 'Y-m-d'): string
    {
        $input = self::string($input, 50);

        $date = \DateTime::createFromFormat($format, $input);

        if (!$date || $date->format($format) !== $input) {
            throw new \InvalidArgumentException("Invalid date format. Expected: {$format}");
        }

        return $input;
    }

    /**
     * Validate array of values
     */
    public static function array(mixed $input, callable $validator): array
    {
        if (!is_array($input)) {
            throw new \InvalidArgumentException('Input must be an array');
        }

        $validated = [];
        foreach ($input as $key => $value) {
            $validated[$key] = $validator($value);
        }

        return $validated;
    }

    /**
     * Validate enum value
     */
    public static function enum(mixed $input, array $allowedValues): string
    {
        $value = self::string($input, 255);

        if (!in_array($value, $allowedValues, true)) {
            $allowed = implode(', ', $allowedValues);
            throw new \InvalidArgumentException("Value must be one of: {$allowed}");
        }

        return $value;
    }
}
