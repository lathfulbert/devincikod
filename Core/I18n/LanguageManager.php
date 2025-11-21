<?php

namespace App\Core\I18n;

use App\Core\I18n\Contracts\TranslatorInterface;

/**
 * Class LanguageManager
 * 
 * Central manager for I18n operations.
 * Manages locale detection, translator initialization, and provides facade methods.
 */
class LanguageManager
{
    protected static ?LanguageManager $instance = null;
    protected Translator $translator;
    protected LanguageLoader $loader;
    protected ?LanguageCache $cache;
    protected string $currentLocale;
    protected string $fallbackLocale;
    protected array $supportedLocales;

    private function __construct()
    {
        $this->initializeFromConfig();
        $this->initializeComponents();
    }

    /**
     * Get singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize configuration from app config.
     */
    protected function initializeFromConfig(): void
    {
        try {
            $app = \App\Core\Application::getInstance();
            $config = $app->config->get('app', []);
        } catch (\Exception $e) {
            // If Application not initialized yet, use defaults
            $config = [];
        }

        $this->currentLocale = $config['locale'] ?? 'fr';
        $this->fallbackLocale = $config['fallback_locale'] ?? 'en';
        $this->supportedLocales = $config['supported_locales'] ?? ['fr', 'en'];
    }

    /**
     * Initialize I18n components.
     */
    protected function initializeComponents(): void
    {
        $basePath = dirname(dirname(__DIR__));

        try {
            $app = \App\Core\Application::getInstance();
            $config = $app->config->get('app', []);
            $i18nConfig = $config['i18n'] ?? [];
        } catch (\Exception $e) {
            $i18nConfig = [];
        }

        // Initialize cache
        if ($i18nConfig['cache_enabled'] ?? true) {
            $cachePath = $basePath . ($i18nConfig['cache_path'] ?? '/storage/cache/i18n');
            $this->cache = new LanguageCache($cachePath);
        }

        // Initialize loader
        $this->loader = new LanguageLoader($basePath, $this->cache, $i18nConfig['cache_enabled'] ?? true);

        // Initialize translator
        $this->translator = new Translator($this->loader, $this->currentLocale, $this->fallbackLocale);
    }

    /**
     * Get the translator instance.
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * Set the current locale.
     */
    public function setLocale(string $locale): void
    {
        if (!$this->isLocaleSupported($locale)) {
            return;
        }

        $this->currentLocale = $locale;
        $this->translator->setLocale($locale);

        // Store in session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['locale'] = $locale;
        }
    }

    /**
     * Get the current locale.
     */
    public function getLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Get the fallback locale.
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    /**
     * Get supported locales.
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    /**
     * Check if a locale is supported.
     */
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales);
    }

    /**
     * Detect locale from various sources.
     * Priority: URL param > Session > User preference > Accept-Language header > Default
     */
    public function detectLocale(): string
    {
        // 1. Check URL parameter
        if (isset($_GET['lang']) && $this->isLocaleSupported($_GET['lang'])) {
            return $_GET['lang'];
        }

        // 2. Check session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['locale'])) {
            if ($this->isLocaleSupported($_SESSION['locale'])) {
                return $_SESSION['locale'];
            }
        }

        // 3. Check user preference (if authenticated)
        $userLocale = $this->getUserPreferredLocale();
        if ($userLocale && $this->isLocaleSupported($userLocale)) {
            return $userLocale;
        }

        // 4. Check Accept-Language header
        $headerLocale = $this->detectLocaleFromHeader();
        if ($headerLocale && $this->isLocaleSupported($headerLocale)) {
            return $headerLocale;
        }

        // 5. Fallback to default
        return $this->currentLocale;
    }

    /**
     * Get user's preferred locale from database.
     */
    protected function getUserPreferredLocale(): ?string
    {
        // Check if user is authenticated
        if (isset($_SESSION['user']['id'])) {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $stmt = $db->query(
                    "SELECT preferred_locale FROM users WHERE id = ?",
                    [$_SESSION['user']['id']]
                );
                $user = $stmt->fetch();

                return $user['preferred_locale'] ?? null;
            } catch (\Exception $e) {
                // Column might not exist yet
                return null;
            }
        }

        return null;
    }

    /**
     * Detect locale from Accept-Language header.
     */
    protected function detectLocaleFromHeader(): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $languages = explode(',', $header);

        foreach ($languages as $language) {
            $locale = substr(trim($language), 0, 2);
            if ($this->isLocaleSupported($locale)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Translate a key (facade method).
     */
    public function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return $this->translator->get($key, $replace, $locale);
    }

    /**
     * Translate with pluralization (facade method).
     */
    public function transChoice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        return $this->translator->choice($key, $count, $replace, $locale);
    }

    /**
     * Check if translation exists.
     */
    public function has(string $key, ?string $locale = null): bool
    {
        return $this->translator->has($key, $locale);
    }

    /**
     * Get all translations for a locale.
     */
    public function all(?string $locale = null): array
    {
        return $this->translator->all($locale);
    }

    /**
     * Invalidate cache.
     */
    public function clearCache(?string $locale = null): void
    {
        $this->loader->invalidateCache($locale);
    }

    /**
     * Get available locales from language files.
     */
    public function getAvailableLocales(): array
    {
        return $this->loader->getAvailableLocales();
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        return $this->cache ? $this->cache->getStats() : [];
    }
}
