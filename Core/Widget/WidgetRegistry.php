<?php

namespace App\Core\Widget;

use App\Core\Widget\Contracts\WidgetInterface;

/**
 * Classe WidgetRegistry
 *
 * Registre central pour tous les widgets du framework.
 * Permet l'enregistrement, la découverte et l'instanciation des widgets.
 */
class WidgetRegistry
{
    private static ?WidgetRegistry $instance = null;
    private array $widgets = [];
    private array $moduleWidgets = [];

    private function __construct()
    {
    }

    /**
     * Récupère l'instance singleton du registre
     *
     * @return WidgetRegistry
     */
    public static function getInstance(): WidgetRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enregistre un widget
     *
     * @param string $module Nom du module
     * @param string $widgetClass Classe du widget (doit implémenter WidgetInterface)
     * @return void
     */
    public function register(string $module, string $widgetClass): void
    {
        if (!class_exists($widgetClass)) {
            throw new \InvalidArgumentException("Widget class {$widgetClass} does not exist");
        }

        if (!is_subclass_of($widgetClass, WidgetInterface::class)) {
            throw new \InvalidArgumentException("Widget class {$widgetClass} must implement WidgetInterface");
        }

        // Créer une instance temporaire pour récupérer le nom
        $widget = new $widgetClass();
        $widgetName = $widget->getName();

        // Enregistrer dans le registre général
        $this->widgets[$widgetName] = [
            'module' => $module,
            'class' => $widgetClass,
            'instance' => null
        ];

        // Enregistrer dans le registre par module
        if (!isset($this->moduleWidgets[$module])) {
            $this->moduleWidgets[$module] = [];
        }
        $this->moduleWidgets[$module][$widgetName] = $widgetClass;
    }

    /**
     * Récupère un widget par son nom
     *
     * @param string $widgetName
     * @return WidgetInterface|null
     */
    public function get(string $widgetName): ?WidgetInterface
    {
        if (!isset($this->widgets[$widgetName])) {
            return null;
        }

        // Utiliser l'instance existante ou en créer une nouvelle
        if ($this->widgets[$widgetName]['instance'] === null) {
            $class = $this->widgets[$widgetName]['class'];
            $this->widgets[$widgetName]['instance'] = new $class();
        }

        return $this->widgets[$widgetName]['instance'];
    }

    /**
     * Récupère tous les widgets d'un module
     *
     * @param string $module
     * @return array<string, WidgetInterface>
     */
    public function getModuleWidgets(string $module): array
    {
        if (!isset($this->moduleWidgets[$module])) {
            return [];
        }

        $widgets = [];
        foreach ($this->moduleWidgets[$module] as $widgetName => $widgetClass) {
            $widgets[$widgetName] = $this->get($widgetName);
        }

        return $widgets;
    }

    /**
     * Récupère tous les widgets enregistrés
     *
     * @return array
     */
    public function getAllWidgets(): array
    {
        $allWidgets = [];
        $statusConfig = [];
        $configPath = __DIR__ . '/../../../config/widgets_status.php';
        if (file_exists($configPath)) {
            $statusConfig = include $configPath;
        }
        foreach (array_keys($this->widgets) as $widgetName) {
            $widget = $this->get($widgetName);
            if ($widget && method_exists($widget, 'isEnabled')) {
                // Si le statut est défini dans la config, il prime
                $enabled = array_key_exists($widgetName, $statusConfig)
                    ? (bool)$statusConfig[$widgetName]
                    : (method_exists($widget, 'isEnabled') ? $widget->isEnabled() : true);
                if ($enabled) {
                    $allWidgets[$widgetName] = $widget;
                }
            }
        }
        return $allWidgets;
    }

    /**
     * Vérifie si un widget existe
     *
     * @param string $widgetName
     * @return bool
     */
    public function has(string $widgetName): bool
    {
        return isset($this->widgets[$widgetName]);
    }

    /**
     * Découvre automatiquement les widgets d'un module
     *
     * @param string $module Nom du module
     * @return int Nombre de widgets découverts
     */
    public function discoverModuleWidgets(string $module): int
    {
        $widgetsPath = __DIR__ . "/../../Modules/{$module}/Widgets";

        if (!is_dir($widgetsPath)) {
            return 0;
        }

        $count = 0;
        $files = scandir($widgetsPath);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $className = pathinfo($file, PATHINFO_FILENAME);
            $fullClassName = "Modules\\{$module}\\Widgets\\{$className}";

            if (class_exists($fullClassName) && is_subclass_of($fullClassName, WidgetInterface::class)) {
                $this->register($module, $fullClassName);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Découvre tous les widgets de tous les modules
     *
     * @return array Nombre de widgets découverts par module
     */
    public function discoverAllWidgets(): array
    {
        $modulesPath = __DIR__ . "/../../Modules";
        $discovered = [];

        if (!is_dir($modulesPath)) {
            return $discovered;
        }

        $modules = scandir($modulesPath);

        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }

            $modulePath = $modulesPath . '/' . $module;
            if (!is_dir($modulePath)) {
                continue;
            }

            $count = $this->discoverModuleWidgets($module);
            if ($count > 0) {
                $discovered[$module] = $count;
            }
        }

        return $discovered;
    }

    /**
     * Récupère les widgets accessibles pour un utilisateur donné
     *
     * @param mixed $user
     * @param string|null $module Filtrer par module (optionnel)
     * @return array<string, WidgetInterface>
     */
    public function getWidgetsForUser($user, ?string $module = null): array
    {
        $widgets = $module ? $this->getModuleWidgets($module) : $this->getAllRegisteredWidgets();
        $accessible = [];

        // Charger le statut depuis la config
        $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'widgets_status.php';
        $fileExists = file_exists($configPath);
        $statusConfig = $fileExists ? include $configPath : [];

            foreach ($widgets as $name => $widget) {
                // Utiliser le nom du widget retourné par getName() pour la correspondance
                $widgetKey = method_exists($widget, 'getName') ? $widget->getName() : $name;
                // Si la clé existe et est false, on exclut systématiquement
                if (array_key_exists($widgetKey, $statusConfig) && !$statusConfig[$widgetKey]) {
                    continue;
                }
                $enabled = (method_exists($widget, 'isEnabled') ? $widget->isEnabled() : true);
                if ($widget && $enabled) {
                    if (method_exists($widget, 'canView')) {
                        if ($widget->canView($user)) {
                            $accessible[$name] = $widget;
                        }
                    } else {
                        $accessible[$name] = $widget;
                    }
                }
            }

        return $accessible;
    }

    /**
     * Retourne tous les widgets enregistrés (activés ou non)
     * @return array<string, WidgetInterface>
     */
    public function getAllRegisteredWidgets(): array
    {
        $all = [];
        foreach ($this->widgets as $widgetName => $meta) {
            $class = $meta['class'];
            $instance = $meta['instance'] ?? new $class();
            $all[$widgetName] = $instance;
        }
        return $all;
    }
}
