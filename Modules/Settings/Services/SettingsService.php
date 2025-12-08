<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\Setting;

class SettingsService
{
    private static ?array $cache = null;

    /**
     * Get setting value
     */
    public function get(string $key, $default = null)
    {
        $this->loadCache();
        return $this->cache[$key] ?? $default;
    }

    /**
     * Set setting value
     */
    public function set(string $key, $value, string $type = 'string', string $group = 'general'): bool
    {
        $result = Setting::set($key, $value, $type, $group);
        $this->clearCache();
        return $result;
    }

    /**
     * Get all settings
     */
    public function all(): array
    {
        $this->loadCache();
        return $this->cache;
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): array
    {
        return Setting::getByGroup($group);
    }

    /**
     * Check if setting exists
     */
    public function has(string $key): bool
    {
        $this->loadCache();
        return isset($this->cache[$key]);
    }

    /**
     * Remove a setting
     */
    public function remove(string $key): bool
    {
        $result = Setting::remove($key);
        $this->clearCache();
        return $result;
    }

    /**
     * Load settings into cache
     */
    private function loadCache(): void
    {
        if (self::$cache === null) {
            self::$cache = Setting::getAll();
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        self::$cache = null;
    }

    /**
     * Export settings to JSON
     */
    public function export(): string
    {
        return json_encode(Setting::all(), JSON_PRETTY_PRINT);
    }

    /**
     * Import settings from JSON
     */
    public function import(string $json): bool
    {
        $data = json_decode($json, true);

        if (!$data) {
            return false;
        }

        foreach ($data as $item) {
            Setting::set(
                $item['key'],
                $item['value'],
                $item['type'] ?? 'string',
                $item['group'] ?? 'general'
            );
        }

        $this->clearCache();
        return true;
    }

    /**
     * Get site settings
     */
    public function getSiteSettings(): array
    {
        return [
            'site_name' => $this->get('site_name', 'SunuFramework'),
            'site_description' => $this->get('site_description', ''),
            'site_logo' => $this->get('site_logo', ''),
            'site_favicon' => $this->get('site_favicon', ''),
            'default_language' => $this->get('default_language', 'fr'),
            'default_timezone' => $this->get('default_timezone', 'Africa/Dakar'),
            'date_format' => $this->get('date_format', 'Y-m-d'),
            'time_format' => $this->get('time_format', 'H:i'),
        ];
    }

    /**
     * Get theme settings
     */
    public function getThemeSettings(): array
    {
        return [
            'theme_mode' => $this->get('theme_mode', 'light'),
            'primary_color' => $this->get('primary_color', '#7366FF'),
            'secondary_color' => $this->get('secondary_color', '#838383'),
            'success_color' => $this->get('success_color', '#65c15c'),
            'sidebar_type' => $this->get('sidebar_type', 'compact-sidebar'),
            'sidebar_icon' => $this->get('sidebar_icon', 'stroke-svg'),
            'layout_type' => $this->get('layout_type', 'ltr'),
            'font_family' => $this->get('font_family', 'Rubik'),
            'font_size' => $this->get('font_size', '14px'),
        ];
    }

    /**
     * Get logo URL (using FileManager)
     */
    public function getLogoUrl(?string $default = null): string
    {
        $logoPath = $this->get('site_logo', '');

        if (empty($logoPath)) {
            return $default ?? url('assets/images/logo/davinci.png');
        }

        // Si le chemin commence par 'storage/', on utilise file_url
        if (str_starts_with($logoPath, 'storage/')) {
            return file_url($logoPath);
        }

        // Sinon, on utilise url() directement
        return url($logoPath);
    }

    /**
     * Get dark logo URL (using FileManager)
     */
    public function getDarkLogoUrl(?string $default = null): string
    {
        $logoPath = $this->get('site_logo_dark', '');

        if (empty($logoPath)) {
            return $default ?? url('assets/images/logo/logo_dark.png');
        }

        if (str_starts_with($logoPath, 'storage/')) {
            return file_url($logoPath);
        }

        return url($logoPath);
    }

    /**
     * Get logo icon URL (using FileManager)
     */
    public function getLogoIconUrl(?string $default = null): string
    {
        $logoPath = $this->get('site_logo_icon', '');

        if (empty($logoPath)) {
            return $default ?? url('assets/images/logo/logo-icon.png');
        }

        if (str_starts_with($logoPath, 'storage/')) {
            return file_url($logoPath);
        }

        return url($logoPath);
    }

    /**
     * Get favicon URL (using FileManager)
     */
    public function getFaviconUrl(?string $default = null): string
    {
        $faviconPath = $this->get('site_favicon', '');

        if (empty($faviconPath)) {
            return $default ?? url('assets/images/favicon.png');
        }

        if (str_starts_with($faviconPath, 'storage/')) {
            return file_url($faviconPath);
        }

        return url($faviconPath);
    }

    /**
     * Get site name
     */
    public function getSiteName(?string $default = null): string
    {
        return $this->get('site_name', $default ?? 'SunuFramework');
    }

    /**
     * Get site description
     */
    public function getSiteDescription(?string $default = null): string
    {
        return $this->get('site_description', $default ?? '');
    }

    /**
     * Get API settings
     */
    public function getApiSettings(): array
    {
        return [
            'openai_api_key' => $this->get('openai_api_key', ''),
            'google_api_key' => $this->get('google_api_key', ''),
            'stripe_api_key' => $this->get('stripe_api_key', ''),
            'paypal_client_id' => $this->get('paypal_client_id', ''),
            'sms_api_key' => $this->get('sms_api_key', ''),
            'map_api_key' => $this->get('map_api_key', ''),
        ];
    }

    /**
     * Get mail settings
     */
    public function getMailSettings(): array
    {
        return [
            'mail_driver' => $this->get('mail_driver', 'smtp'),
            'mail_host' => $this->get('mail_host', ''),
            'mail_port' => $this->get('mail_port', '587'),
            'mail_username' => $this->get('mail_username', ''),
            'mail_password' => $this->get('mail_password', ''),
            'mail_encryption' => $this->get('mail_encryption', 'tls'),
            'mail_from_address' => $this->get('mail_from_address', ''),
            'mail_from_name' => $this->get('mail_from_name', ''),
        ];
    }
}
