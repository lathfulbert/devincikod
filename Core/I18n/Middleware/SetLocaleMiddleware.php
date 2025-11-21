<?php

namespace App\Core\I18n\Middleware;

use App\Core\I18n\LanguageManager;

/**
 * Class SetLocaleMiddleware
 * 
 * Middleware that detects and sets the locale for each request.
 * Priority: URL param > Session > User preference > Accept-Language header > Default
 */
class SetLocaleMiddleware
{
    protected LanguageManager $languageManager;

    public function __construct()
    {
        $this->languageManager = LanguageManager::getInstance();
    }

    /**
     * Handle the request and set the locale.
     */
    public function handle(): bool
    {
        // Detect locale from various sources
        $locale = $this->languageManager->detectLocale();

        // Set the detected locale
        $this->languageManager->setLocale($locale);

        return true;
    }

    /**
     * Static method for easy middleware registration.
     */
    public static function run(): bool
    {
        $middleware = new self();
        return $middleware->handle();
    }
}
