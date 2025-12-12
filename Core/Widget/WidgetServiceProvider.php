<?php

namespace App\Core\Widget;

/**
 * Classe WidgetServiceProvider
 *
 * Service Provider pour initialiser le système de widgets
 * Découvre automatiquement tous les widgets et les enregistre
 */
class WidgetServiceProvider
{
    private static bool $initialized = false;

    /**
     * Initialise le système de widgets
     *
     * @return void
     */
    public static function boot(): void
    {
        if (self::$initialized) {
            return;
        }

        // Charger les helpers
        self::loadHelpers();

        // Découvrir et enregistrer tous les widgets
        self::discoverWidgets();

        // Enregistrer les routes API
        self::registerRoutes();

        self::$initialized = true;
    }

    /**
     * Charge les fonctions helper
     *
     * @return void
     */
    private static function loadHelpers(): void
    {
        $helpersFile = __DIR__ . '/helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }

    /**
     * Découvre automatiquement tous les widgets
     *
     * @return void
     */
    private static function discoverWidgets(): void
    {
        try {
            $registry = WidgetRegistry::getInstance();
            $discovered = $registry->discoverAllWidgets();

            if (!empty($discovered)) {
                $total = array_sum($discovered);
                error_log("Widget System: Discovered {$total} widgets from " . count($discovered) . " modules");
            }
        } catch (\Exception $e) {
            error_log("Widget System: Error during discovery - " . $e->getMessage());
        }
    }

    /**
     * Enregistre les routes API pour les widgets
     *
     * @return void
     */
    private static function registerRoutes(): void
    {
        // Note: Les routes doivent être enregistrées dans le fichier de routes principal
        // Cette méthode est un placeholder pour une future implémentation
    }

    /**
     * Créer le répertoire de cache s'il n'existe pas
     *
     * @return void
     */
    public static function ensureCacheDirectory(): void
    {
        $cacheDir = __DIR__ . '/../../storage/cache/widgets';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
    }

    /**
     * Nettoie tous les caches des widgets
     *
     * @return int Nombre de fichiers supprimés
     */
    public static function clearAllCache(): int
    {
        $cacheDir = __DIR__ . '/../../storage/cache/widgets';
        if (!is_dir($cacheDir)) {
            return 0;
        }

        $count = 0;
        $files = glob($cacheDir . '/*.json');

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Retourne les statistiques du système de widgets
     *
     * @return array
     */
    public static function getStats(): array
    {
        $registry = WidgetRegistry::getInstance();
        $allWidgets = $registry->getAllWidgets();

        $stats = [
            'total_widgets' => count($allWidgets),
            'by_type' => [],
            'by_module' => [],
            'cacheable' => 0,
            'with_permission' => 0
        ];

        foreach ($allWidgets as $widget) {
            $config = $widget->getConfig();

            // Par type
            $type = $config['type'] ?? 'unknown';
            if (!isset($stats['by_type'][$type])) {
                $stats['by_type'][$type] = 0;
            }
            $stats['by_type'][$type]++;

            // Par module
            $module = explode('.', $widget->getName())[0] ?? 'unknown';
            if (!isset($stats['by_module'][$module])) {
                $stats['by_module'][$module] = 0;
            }
            $stats['by_module'][$module]++;

            // Cacheable
            if ($widget->isCacheable()) {
                $stats['cacheable']++;
            }

            // Avec permission
            if ($widget->getPermission() !== null) {
                $stats['with_permission']++;
            }
        }

        return $stats;
    }
}
