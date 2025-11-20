<?php

namespace App\Core\Security;

class Sanitizer
{
    /**
     * Nettoie une donnée selon son type
     */
    public static function clean($input, string $type = 'string')
    {
        if ($input === null) {
            return null;
        }

        return match ($type) {
            'string' => self::cleanString($input),
            'email' => self::cleanEmail($input),
            'url' => self::cleanUrl($input),
            'int', 'integer' => self::cleanInt($input),
            'float' => self::cleanFloat($input),
            'bool', 'boolean' => self::cleanBool($input),
            'html' => self::sanitizeHtml($input),
            'alpha' => self::cleanAlpha($input),
            'alphanumeric' => self::cleanAlphanumeric($input),
            default => self::cleanString($input),
        };
    }

    /**
     * Nettoie un tableau de données selon des règles
     */
    public static function cleanArray(array $data, array $rules = []): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            $type = $rules[$key] ?? 'string';
            $cleaned[$key] = is_array($value)
                ? self::cleanArray($value, $rules[$key] ?? [])
                : self::clean($value, $type);
        }
        return $cleaned;
    }

    /**
     * Nettoie une chaîne de caractères
     */
    private static function cleanString($input): string
    {
        if (is_array($input)) {
            return '';
        }
        return trim(strip_tags((string) $input));
    }

    /**
     * Nettoie et valide un email
     */
    private static function cleanEmail($input): ?string
    {
        $email = filter_var($input, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Nettoie et valide une URL
     */
    private static function cleanUrl($input): ?string
    {
        $url = filter_var($input, FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    /**
     * Nettoie et convertit en entier
     */
    private static function cleanInt($input): int
    {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Nettoie et convertit en float
     */
    private static function cleanFloat($input): float
    {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Convertit en booléen
     */
    private static function cleanBool($input): bool
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Ne garde que les lettres
     */
    private static function cleanAlpha($input): string
    {
        return preg_replace('/[^a-zA-Z]/', '', (string) $input);
    }

    /**
     * Ne garde que les lettres et chiffres
     */
    private static function cleanAlphanumeric($input): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', (string) $input);
    }

    /**
     * Nettoie le HTML en conservant certaines balises
     */
    public static function sanitizeHtml(string $html, array $allowedTags = []): string
    {
        if (empty($allowedTags)) {
            return strip_tags($html);
        }

        $allowedString = '<' . implode('><', $allowedTags) . '>';
        return strip_tags($html, $allowedString);
    }

    /**
     * Échappe une chaîne pour l'affichage HTML (protection XSS)
     */
    public static function escapeOutput(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Échappe pour les attributs HTML
     */
    public static function escapeAttribute(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Échappe pour JavaScript
     */
    public static function escapeJs(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
}
