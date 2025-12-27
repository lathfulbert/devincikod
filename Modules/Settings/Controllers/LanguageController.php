<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;

class LanguageController
{
    /**
     * Récupérer la liste des langues disponibles depuis la configuration
     */
    private function getAvailableLanguages(): array
    {
        $languages = config('languages');

        // Si le fichier n'a pas été chargé, le charger manuellement
        if (empty($languages)) {
            $configFile = app()->getBasePath() . '/config/languages.php';
            if (file_exists($configFile)) {
                $languages = require $configFile;
                // Sauvegarder dans la config pour les prochains appels
                app()->config->set('languages', $languages);
            }
        }

        return $languages ?? [];
    }

    /**
     * Afficher la liste des langues
     */
    public function index()
    {
        $app = Application::getInstance();

        // Récupérer les langues supportées depuis la config
        $supportedLocales = config('app.supported_locales', ['fr', 'en', 'ar']);
        $defaultLocale = config('app.locale', 'fr');
        $fallbackLocale = config('app.fallback_locale', 'en');

        // Construire la liste des langues avec leur statut
        $languages = [];
        $availableLanguages = $this->getAvailableLanguages();

        foreach ($availableLanguages as $code => $lang) {
            $languages[$code] = array_merge($lang, [
                'is_active' => in_array($code, $supportedLocales),
                'is_default' => $code === $defaultLocale,
                'is_fallback' => $code === $fallbackLocale,
                'has_file' => file_exists(app()->getBasePath() . "/languages/{$code}.json")
            ]);
        }

        return view('settings/languages/index', [
            'title' => 'Gestion des Langues',
            'languages' => $languages,
            'default_locale' => $defaultLocale,
            'fallback_locale' => $fallbackLocale,
        ]);
    }

    /**
     * Activer une langue
     */
    public function activate()
    {
        $code = $_POST['code'] ?? null;
        $availableLanguages = $this->getAvailableLanguages();

        if (!$code || !isset($availableLanguages[$code])) {
            $_SESSION['flash']['error'] = 'Code de langue invalide.';
            redirect('/admin/settings/languages');
        }

        $supportedLocales = config('app.supported_locales', ['fr', 'en', 'ar']);

        if (!in_array($code, $supportedLocales)) {
            $supportedLocales[] = $code;
            $this->updateConfig('supported_locales', $supportedLocales);

            // Créer le fichier de langue s'il n'existe pas
            $this->ensureLanguageFile($code);

            $_SESSION['flash']['success'] = "La langue {$code} a été activée avec succès.";
        } else {
            $_SESSION['flash']['warning'] = "La langue {$code} est déjà active.";
        }

        redirect('/admin/settings/languages');
    }

    /**
     * Désactiver une langue
     */
    public function deactivate()
    {
        $code = $_POST['code'] ?? null;

        if (!$code) {
            $_SESSION['flash']['error'] = 'Code de langue invalide.';
            redirect('/admin/settings/languages');
        }

        $defaultLocale = config('app.locale', 'fr');
        $fallbackLocale = config('app.fallback_locale', 'en');

        // Ne pas désactiver la langue par défaut ou de fallback
        if ($code === $defaultLocale) {
            $_SESSION['flash']['error'] = 'Vous ne pouvez pas désactiver la langue par défaut.';
            redirect('/admin/settings/languages');
        }

        if ($code === $fallbackLocale) {
            $_SESSION['flash']['error'] = 'Vous ne pouvez pas désactiver la langue de fallback.';
            redirect('/admin/settings/languages');
        }

        $supportedLocales = config('app.supported_locales', ['fr', 'en', 'ar']);
        $supportedLocales = array_diff($supportedLocales, [$code]);

        $this->updateConfig('supported_locales', array_values($supportedLocales));

        $_SESSION['flash']['success'] = "La langue {$code} a été désactivée avec succès.";
        redirect('/admin/settings/languages');
    }

    /**
     * Définir la langue par défaut
     */
    public function setDefault()
    {
        $code = $_POST['code'] ?? null;

        if (!$code) {
            $_SESSION['flash']['error'] = 'Code de langue invalide.';
            redirect('/admin/settings/languages');
        }

        $supportedLocales = config('app.supported_locales', ['fr', 'en', 'ar']);

        if (!in_array($code, $supportedLocales)) {
            $_SESSION['flash']['error'] = 'Vous devez d\'abord activer cette langue.';
            redirect('/admin/settings/languages');
        }

        $this->updateConfig('locale', $code);

        $_SESSION['flash']['success'] = "La langue {$code} est maintenant la langue par défaut.";
        redirect('/admin/settings/languages');
    }

    /**
     * Définir la langue de fallback
     */
    public function setFallback()
    {
        $code = $_POST['code'] ?? null;

        if (!$code) {
            $_SESSION['flash']['error'] = 'Code de langue invalide.';
            redirect('/admin/settings/languages');
        }

        $supportedLocales = config('app.supported_locales', ['fr', 'en', 'ar']);

        if (!in_array($code, $supportedLocales)) {
            $_SESSION['flash']['error'] = 'Vous devez d\'abord activer cette langue.';
            redirect('/admin/settings/languages');
        }

        $this->updateConfig('fallback_locale', $code);

        $_SESSION['flash']['success'] = "La langue {$code} est maintenant la langue de fallback.";
        redirect('/admin/settings/languages');
    }

    /**
     * Créer le fichier de langue à partir d'un modèle
     */
    public function createFile()
    {
        $code = $_POST['code'] ?? null;
        $availableLanguages = $this->getAvailableLanguages();

        if (!$code || !isset($availableLanguages[$code])) {
            $_SESSION['flash']['error'] = 'Code de langue invalide.';
            redirect('/admin/settings/languages');
        }

        $created = $this->ensureLanguageFile($code);

        if ($created) {
            $_SESSION['flash']['success'] = "Le fichier de langue {$code}.json a été créé avec succès.";
        } else {
            $_SESSION['flash']['warning'] = "Le fichier de langue {$code}.json existe déjà.";
        }

        redirect('/admin/settings/languages');
    }

    /**
     * Mettre à jour la configuration dans config/app.php
     */
    private function updateConfig(string $key, $value): void
    {
        $configFile = app()->getBasePath() . '/config/app.php';

        if (!file_exists($configFile)) {
            return;
        }

        $content = file_get_contents($configFile);

        // Mettre à jour la configuration
        if ($key === 'supported_locales') {
            $valueStr = "['" . implode("', '", $value) . "']";
            $pattern = "/'supported_locales'\s*=>\s*\[[^\]]*\]/";
            $replacement = "'supported_locales' => $valueStr";
        } else {
            $valueStr = is_string($value) ? "'$value'" : var_export($value, true);
            $pattern = "/'$key'\s*=>\s*'[^']*'/";
            $replacement = "'$key' => $valueStr";
        }

        $content = preg_replace($pattern, $replacement, $content);

        file_put_contents($configFile, $content);

        // Vider le cache de config si nécessaire
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($configFile, true);
        }
    }

    /**
     * Créer le fichier de langue s'il n'existe pas
     */
    private function ensureLanguageFile(string $code): bool
    {
        $filePath = app()->getBasePath() . "/languages/{$code}.json";

        if (file_exists($filePath)) {
            return false;
        }

        // Créer le répertoire si nécessaire
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Modèle de base pour un nouveau fichier de langue
        $template = [
            'app' => [
                'name' => 'SunuFramework',
                'tagline' => 'Modern modular PHP framework'
            ],
            'auth' => [
                'login' => 'Login',
                'logout' => 'Logout',
                'register' => 'Register',
                'welcome' => 'Welcome, :name'
            ],
            'dashboard' => [
                'title' => 'Dashboard',
                'welcome' => 'Welcome to your dashboard'
            ],
            'navigation' => [
                'home' => 'Home',
                'dashboard' => 'Dashboard',
                'users' => 'Users',
                'settings' => 'Settings'
            ],
            'actions' => [
                'save' => 'Save',
                'cancel' => 'Cancel',
                'delete' => 'Delete',
                'edit' => 'Edit',
                'create' => 'Create',
                'view' => 'View'
            ],
            'messages' => [
                'success' => 'Operation successful!',
                'error' => 'An error occurred.',
                'saved' => 'Saved successfully.',
                'deleted' => 'Deleted successfully.'
            ]
        ];

        file_put_contents($filePath, json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return true;
    }
}
