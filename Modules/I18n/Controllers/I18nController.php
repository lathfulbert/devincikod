<?php

namespace Modules\I18n\Controllers;

use App\Core\Application;
use App\Core\I18n\LanguageManager;

/**
 * Class I18nController
 * 
 * Backoffice controller for managing translations.
 */
class I18nController
{
    protected LanguageManager $manager;

    public function __construct()
    {
        $this->manager = LanguageManager::getInstance();
    }

    /**
     * Index: List all translations.
     */
    public function index()
    {
        $app = Application::getInstance();
        $locale = $_GET['locale'] ?? $this->manager->getLocale();

        // Valid locale check
        if (!$this->manager->isLocaleSupported($locale)) {
            $locale = $this->manager->getLocale();
        }

        $translations = $this->manager->all($locale);
        $flatTranslations = $this->flattenTranslations($translations);

        return view('i18n/index', [
            'title' => 'Gestion des Traductions',
            'translations' => $flatTranslations,
            'current_locale' => $locale,
            'supported_locales' => $this->manager->getSupportedLocales(),
            'cache_stats' => $this->manager->getCacheStats()
        ]);
    }

    /**
     * Edit a translation key.
     */
    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update();
            return;
        }

        $app = Application::getInstance();
        $key = $_GET['key'] ?? '';
        $locale = $_GET['locale'] ?? $this->manager->getLocale();

        if (empty($key)) {
            flash('danger', 'Clé de traduction manquante.');
            redirect('/admin/i18n');
            return;
        }

        $value = $this->manager->has($key, $locale)
            ? $this->manager->trans($key, [], $locale)
            : '';

        return view('i18n/edit', [
            'title' => 'Modifier la Traduction',
            'key' => $key,
            'value' => $value,
            'locale' => $locale
        ]);
    }

    /**
     * Update a translation.
     */
    protected function update(): void
    {
        $key = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';
        $locale = $_POST['locale'] ?? $this->manager->getLocale();

        if (empty($key)) {
            flash('danger', 'Clé de traduction manquante.');
            redirect('/admin/i18n');
            return;
        }

        // Load current overrides
        $basePath = dirname(dirname(dirname(__DIR__)));
        $overridePath = $basePath . "/storage/i18n/overrides";
        $overrideFile = "{$overridePath}/{$locale}.json";

        if (!is_dir($overridePath)) {
            mkdir($overridePath, 0755, true);
        }

        $overrides = [];
        if (file_exists($overrideFile)) {
            $content = file_get_contents($overrideFile);
            $overrides = json_decode($content, true) ?? [];
        }

        // Set value using dot notation
        $this->setDotNotation($overrides, $key, $value);

        // Save overrides
        $json = json_encode($overrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($overrideFile, $json);

        // Clear cache
        $this->manager->clearCache($locale);

        flash('success', 'Traduction mise à jour avec succès.');
        redirect('/admin/i18n?locale=' . $locale);
    }

    /**
     * Create a new translation.
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        $app = Application::getInstance();
        $locale = $_GET['locale'] ?? $this->manager->getLocale();

        return view('i18n/create', [
            'title' => 'Nouvelle Traduction',
            'locale' => $locale
        ]);
    }

    /**
     * Store a new translation.
     */
    protected function store(): void
    {
        $key = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';
        $locale = $_POST['locale'] ?? $this->manager->getLocale();

        if (empty($key) || empty($value)) {
            flash('danger', 'Clé et valeur sont obligatoires.');
            redirect('/admin/i18n/create?locale=' . $locale);
            return;
        }

        // Same logic as update
        $this->update();
    }

    /**
     * Delete a translation (remove override).
     */
    public function delete(): void
    {
        $key = $_POST['key'] ?? '';
        $locale = $_POST['locale'] ?? $this->manager->getLocale();

        if (empty($key)) {
            flash('danger', 'Clé de traduction manquante.');
            redirect('/admin/i18n');
            return;
        }

        // Load current overrides
        $basePath = dirname(dirname(dirname(__DIR__)));
        $overridePath = $basePath . "/storage/i18n/overrides";
        $overrideFile = "{$overridePath}/{$locale}.json";

        if (file_exists($overrideFile)) {
            $content = file_get_contents($overrideFile);
            $overrides = json_decode($content, true) ?? [];

            // Remove value using dot notation
            $this->unsetDotNotation($overrides, $key);

            // Save overrides
            $json = json_encode($overrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($overrideFile, $json);

            // Clear cache
            $this->manager->clearCache($locale);

            flash('success', 'Override supprimé avec succès.');
        } else {
            flash('warning', 'Aucun override trouvé pour cette clé.');
        }

        redirect('/admin/i18n?locale=' . $locale);
    }

    /**
     * Clear cache.
     */
    public function clearCache(): void
    {
        $locale = $_POST['locale'] ?? null;

        if ($locale) {
            $this->manager->clearCache($locale);
            flash('success', "Cache vidé pour la langue: {$locale}");
        } else {
            $this->manager->clearCache();
            flash('success', 'Tous les caches ont été vidés.');
        }

        redirect('/admin/i18n');
    }

    /**
     * Export translations.
     */
    public function export(): void
    {
        $locale = $_GET['locale'] ?? $this->manager->getLocale();
        $translations = $this->manager->all($locale);

        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="translations_' . $locale . '_' . date('Y-m-d') . '.json"');
        echo $json;
        exit;
    }

    /**
     * Import translations.
     */
    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/i18n');
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            flash('danger', 'Erreur lors de l\'upload du fichier.');
            redirect('/admin/i18n');
            return;
        }

        $locale = $_POST['locale'] ?? $this->manager->getLocale();
        $content = file_get_contents($_FILES['file']['tmp_name']);
        $translations = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            flash('danger', 'Fichier JSON invalide.');
            redirect('/admin/i18n');
            return;
        }

        // Save as override
        $basePath = dirname(dirname(dirname(__DIR__)));
        $overridePath = $basePath . "/storage/i18n/overrides";
        $overrideFile = "{$overridePath}/{$locale}.json";

        if (!is_dir($overridePath)) {
            mkdir($overridePath, 0755, true);
        }

        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($overrideFile, $json);

        // Clear cache
        $this->manager->clearCache($locale);

        flash('success', 'Traductions importées avec succès.');
        redirect('/admin/i18n?locale=' . $locale);
    }

    /**
     * Flatten nested translations array.
     */
    protected function flattenTranslations(array $translations, string $prefix = ''): array
    {
        $flat = [];

        foreach ($translations as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $flat = array_merge($flat, $this->flattenTranslations($value, $fullKey));
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }

    /**
     * Set value in array using dot notation.
     */
    protected function setDotNotation(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        // Set the last key to the value
        $lastKey = array_pop($keys);
        $current = &$array;

        foreach ($keys as $k) {
            $current = &$current[$k];
        }

        $current[$lastKey] = $value;
    }

    /**
     * Unset value in array using dot notation.
     */
    protected function unsetDotNotation(array &$array, string $key): void
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return;
            }
            $current = &$current[$k];
        }

        unset($current[$lastKey]);
    }
    /**
     * Set the application locale.
     */
    public function setLocale(): void
    {
        $locale = $_GET['locale'] ?? null;

        if ($locale && $this->manager->isLocaleSupported($locale)) {
            $this->manager->setLocale($locale);
            flash('success', 'Langue changée avec succès.');
        } else {
            flash('danger', 'Langue non supportée.');
        }

        // Redirect back
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}
