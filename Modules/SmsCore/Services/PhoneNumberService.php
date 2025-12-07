<?php

namespace Modules\SmsCore\Services;

use Modules\Settings\Models\Setting;

class PhoneNumberService
{
    /**
     * Format a phone number with country code prefix
     *
     * @param string $number The phone number to format
     * @param string|null $defaultCountryCode Override default country code from settings
     * @return string Formatted phone number
     */
    public static function format(string $number, ?string $defaultCountryCode = null): string
    {
        // Remove all spaces, dashes, and dots
        $number = preg_replace('/[\s\-\.]/', '', trim($number));

        // If empty, return as is
        if (empty($number)) {
            return $number;
        }

        // Check if auto prefix is enabled
        $autoAddPrefix = Setting::get('sms_auto_add_prefix', true);

        if (!$autoAddPrefix) {
            return $number;
        }

        // Get default country code from settings or parameter
        if ($defaultCountryCode === null) {
            $defaultCountryCode = Setting::get('sms_default_country_code', '+225');
        }

        // Ensure it is not null (in case setting exists but is null)
        if ($defaultCountryCode === null) {
            $defaultCountryCode = '+225';
        }

        // Remove + from default code for comparison
        $codeWithoutPlus = ltrim($defaultCountryCode, '+');

        // Already has + prefix - return as is
        if (str_starts_with($number, '+')) {
            return $number;
        }

        // Has 00 prefix (international format without +) - convert to +
        if (str_starts_with($number, '00')) {
            return '+' . substr($number, 2);
        }

        // Has country code without + (e.g., 225...)
        if (str_starts_with($number, $codeWithoutPlus)) {
            return '+' . $number;
        }

        // Local number - add full prefix
        return $defaultCountryCode . $number;
    }

    /**
     * Format multiple phone numbers
     *
     * @param array $numbers Array of phone numbers
     * @param string|null $defaultCountryCode Override default country code
     * @return array Array of formatted phone numbers
     */
    public static function formatMultiple(array $numbers, ?string $defaultCountryCode = null): array
    {
        return array_map(function ($number) use ($defaultCountryCode) {
            return self::format($number, $defaultCountryCode);
        }, $numbers);
    }

    /**
     * Validate phone number format
     *
     * @param string $number Phone number to validate
     * @return bool True if valid
     */
    public static function validate(string $number): bool
    {
        // Remove spaces, dashes, dots
        $cleaned = preg_replace('/[\s\-\.]/', '', trim($number));

        // Must have at least 8 digits
        if (strlen($cleaned) < 8) {
            return false;
        }

        // Must start with + or digits
        if (!preg_match('/^[\+0-9]/', $cleaned)) {
            return false;
        }

        // Must contain only valid characters
        if (!preg_match('/^[\+0-9]+$/', $cleaned)) {
            return false;
        }

        return true;
    }

    /**
     * Parse multiple phone numbers from text
     * Supports comma-separated, newline-separated, or space-separated
     *
     * @param string $text Text containing phone numbers
     * @return array Array of phone numbers
     */
    public static function parseMultiple(string $text): array
    {
        // Replace newlines and commas with spaces
        $text = str_replace(["\n", "\r", ",", ";"], " ", $text);

        // Split by spaces
        $numbers = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Filter empty values
        return array_filter($numbers, function ($num) {
            return !empty(trim($num));
        });
    }

    /**
     * Remove duplicate phone numbers
     *
     * @param array $numbers Array of phone numbers
     * @return array Array without duplicates
     */
    public static function removeDuplicates(array $numbers): array
    {
        // Format all numbers first for accurate deduplication
        $formatted = self::formatMultiple($numbers);

        // Remove duplicates
        return array_values(array_unique($formatted));
    }

    /**
     * Get list of supported countries
     *
     * @return array Associative array of country code => phone prefix
     */
    public static function getSupportedCountries(): array
    {
        $countries = Setting::get('sms_supported_countries', []);

        if (is_string($countries)) {
            $countries = json_decode($countries, true) ?? [];
        }

        // Default countries if none configured
        if (empty($countries)) {
            return [
                'CI' => '+225', // Côte d'Ivoire
                'SN' => '+221', // Sénégal
                'ML' => '+223', // Mali
                'BF' => '+226', // Burkina Faso
                'TG' => '+228', // Togo
                'NE' => '+227', // Niger
                'BJ' => '+229', // Bénin
            ];
        }

        return $countries;
    }

    /**
     * Get country name from code
     *
     * @param string $code Country code (e.g., 'CI')
     * @return string Country name
     */
    public static function getCountryName(string $code): string
    {
        $countryNames = [
            'CI' => 'Côte d\'Ivoire',
            'SN' => 'Sénégal',
            'ML' => 'Mali',
            'BF' => 'Burkina Faso',
            'TG' => 'Togo',
            'NE' => 'Niger',
            'BJ' => 'Bénin',
            'GH' => 'Ghana',
            'NG' => 'Nigeria',
            'CM' => 'Cameroun',
        ];

        return $countryNames[$code] ?? $code;
    }

    /**
     * Extract country code from phone number
     *
     * @param string $number Phone number
     * @return string|null Country code or null if not found
     */
    public static function extractCountryCode(string $number): ?string
    {
        if (!str_starts_with($number, '+')) {
            return null;
        }

        $countries = self::getSupportedCountries();

        foreach ($countries as $code => $prefix) {
            if (str_starts_with($number, $prefix)) {
                return $code;
            }
        }

        return null;
    }
}
