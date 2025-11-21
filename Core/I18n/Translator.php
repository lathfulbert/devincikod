<?php

namespace App\Core\I18n;

use App\Core\I18n\Contracts\TranslatorInterface;

/**
 * Class Translator
 * 
 * Main translation engine that retrieves and processes translations.
 * Handles parameter replacement and pluralization.
 */
class Translator implements TranslatorInterface
{
    protected string $locale;
    protected string $fallbackLocale;
    protected array $loaded = [];
    protected LanguageLoader $loader;

    public function __construct(LanguageLoader $loader, string $locale, string $fallbackLocale = 'en')
    {
        $this->loader = $loader;
        $this->locale = $locale;
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * Get the translation for the given key.
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        // Load translations if not already loaded
        $this->loadTranslations($locale);

        // Get translation value
        $translation = $this->getTranslationValue($key, $locale);

        // If not found and locale is not fallback, try fallback
        if ($translation === $key && $locale !== $this->fallbackLocale) {
            $this->loadTranslations($this->fallbackLocale);
            $translation = $this->getTranslationValue($key, $this->fallbackLocale);
        }

        // Replace parameters
        return $this->makeReplacements($translation, $replace);
    }

    /**
     * Check if a translation exists for the given key.
     */
    public function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;
        $this->loadTranslations($locale);

        return $this->getTranslationValue($key, $locale) !== $key;
    }

    /**
     * Set the current locale.
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Get the current locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get the fallback locale.
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    /**
     * Set the fallback locale.
     */
    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    /**
     * Get translation with pluralization support.
     */
    public function choice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        $translation = $this->get($key, $replace, $locale);

        // Handle pluralization
        $translation = $this->handlePluralization($translation, $count);

        // Add count to replacements if not already present
        if (!isset($replace['count'])) {
            $replace['count'] = $count;
        }

        return $this->makeReplacements($translation, $replace);
    }

    /**
     * Load translations for a specific locale.
     */
    protected function loadTranslations(string $locale): void
    {
        if (isset($this->loaded[$locale])) {
            return;
        }

        $this->loaded[$locale] = $this->loader->load($locale);
    }

    /**
     * Get translation value from loaded translations using dot notation.
     */
    protected function getTranslationValue(string $key, string $locale): string
    {
        $keys = explode('.', $key);
        $value = $this->loaded[$locale] ?? [];

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $key; // Return key if not found
            }
            $value = $value[$segment];
        }

        return is_string($value) ? $value : $key;
    }

    /**
     * Make parameter replacements in translation.
     * Supports both :parameter and {parameter} syntax.
     */
    protected function makeReplacements(string $translation, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $translation = str_replace(
                [':' . $key, '{' . $key . '}', ':' . strtoupper($key), '{' . strtoupper($key) . '}'],
                [$value, $value, strtoupper($value), strtoupper($value)],
                $translation
            );
        }

        return $translation;
    }

    /**
     * Handle pluralization in translation string.
     * Format: "singular text|plural text"
     */
    protected function handlePluralization(string $translation, int $count): string
    {
        if (!str_contains($translation, '|')) {
            return $translation;
        }

        $parts = explode('|', $translation);

        // Simple pluralization: 0 or 1 = singular, > 1 = plural
        return $count <= 1 ? trim($parts[0]) : trim($parts[1] ?? $parts[0]);
    }

    /**
     * Get all loaded translations for a locale.
     */
    public function all(?string $locale = null): array
    {
        $locale = $locale ?? $this->locale;
        $this->loadTranslations($locale);

        return $this->loaded[$locale] ?? [];
    }
}
