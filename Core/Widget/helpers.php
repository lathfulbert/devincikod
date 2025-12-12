<?php

use App\Core\Widget\WidgetRenderer;
use App\Core\Widget\WidgetRegistry;

if (!function_exists('widget')) {
    /**
     * Rend un widget unique
     *
     * @param string $widgetName
     * @param array $options
     * @return string
     */
    function widget(string $widgetName, array $options = []): string
    {
        $renderer = new WidgetRenderer();
        return $renderer->render($widgetName, $options);
    }
}

if (!function_exists('module_widgets')) {
    /**
     * Rend tous les widgets d'un module
     *
     * @param string $module
     * @param array $options
     * @return string
     */
    function module_widgets(string $module, array $options = []): string
    {
        $renderer = new WidgetRenderer();
        return $renderer->renderModule($module, $options);
    }
}

if (!function_exists('all_widgets')) {
    /**
     * Rend tous les widgets disponibles
     *
     * @param array $options
     * @return string
     */
    function all_widgets(array $options = []): string
    {
        $renderer = new WidgetRenderer();
        return $renderer->renderAll($options);
    }
}

if (!function_exists('widget_data')) {
    /**
     * Récupère uniquement les données d'un widget (sans rendu HTML)
     *
     * @param string $widgetName
     * @return array|null
     */
    function widget_data(string $widgetName): ?array
    {
        $registry = WidgetRegistry::getInstance();
        $widget = $registry->get($widgetName);

        if (!$widget) {
            return null;
        }

        try {
            return $widget->getData();
        } catch (\Exception $e) {
            error_log("Widget data error: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('register_widget')) {
    /**
     * Enregistre un widget dans le registre
     *
     * @param string $module
     * @param string $widgetClass
     * @return void
     */
    function register_widget(string $module, string $widgetClass): void
    {
        $registry = WidgetRegistry::getInstance();
        $registry->register($module, $widgetClass);
    }
}
