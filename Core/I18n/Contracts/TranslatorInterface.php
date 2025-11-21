<?php

namespace App\Core\I18n\Contracts;

/**
 * Interface TranslatorInterface
 * 
 * Defines the contract for translation services.
 * Inspired by Laravel's Translator interface.
 */
interface TranslatorInterface
{
    /**
     * Get the translation for the given key.
     *
     * @param string $key Translation key (dot notation)
     * @param array $replace Replacement parameters
     * @param string|null $locale Specific locale (null = current locale)
     * @return string Translated string
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string;

    /**
     * Check if a translation exists for the given key.
     *
     * @param string $key Translation key
     * @param string|null $locale Specific locale
     * @return bool True if translation exists
     */
    public function has(string $key, ?string $locale = null): bool;

    /**
     * Set the current locale.
     *
     * @param string $locale Locale code (e.g., 'fr', 'en')
     * @return void
     */
    public function setLocale(string $locale): void;

    /**
     * Get the current locale.
     *
     * @return string Current locale code
     */
    public function getLocale(): string;

    /**
     * Get the fallback locale.
     *
     * @return string Fallback locale code
     */
    public function getFallbackLocale(): string;

    /**
     * Set the fallback locale.
     *
     * @param string $locale Fallback locale code
     * @return void
     */
    public function setFallbackLocale(string $locale): void;

    /**
     * Get translation with pluralization support.
     *
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $replace Replacement parameters
     * @param string|null $locale Specific locale
     * @return string Translated string
     */
    public function choice(string $key, int $count, array $replace = [], ?string $locale = null): string;
}
