<?php

namespace App\Core\Widget;

use App\Core\Application;

/**
 * Classe WidgetController
 *
 * Contrôleur API pour la gestion et l'affichage des widgets.
 * Fournit les endpoints REST pour interagir avec les widgets.
 */
class WidgetController
{
    private WidgetRegistry $registry;

    public function __construct()
    {
        $this->registry = WidgetRegistry::getInstance();
    }

    /**
     * Liste tous les widgets disponibles
     * GET /api/widgets
     *
     * @return void
     */
    public function index(): void
    {
        $app = Application::getInstance();
        $user = $app->auth?->user();

        // Récupérer tous les widgets accessibles pour l'utilisateur
        $widgets = $this->registry->getWidgetsForUser($user);

        $response = [
            'success' => true,
            'data' => array_map(function ($widget) {
                return $widget->getConfig();
            }, $widgets),
            'count' => count($widgets)
        ];

        $this->jsonResponse($response);
    }

    /**
     * Liste les widgets d'un module spécifique
     * GET /api/widgets/module/{module}
     *
     * @param array $params
     * @return void
     */
    public function moduleWidgets(array $params = []): void
    {
        $module = $params['module'] ?? null;

        if (!$module) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Module name is required'
            ], 400);
            return;
        }

        $app = Application::getInstance();
        $user = $app->auth?->user();

        // Récupérer les widgets du module accessibles pour l'utilisateur
        $widgets = $this->registry->getWidgetsForUser($user, $module);

        $response = [
            'success' => true,
            'module' => $module,
            'data' => array_map(function ($widget) {
                return $widget->getConfig();
            }, $widgets),
            'count' => count($widgets)
        ];

        $this->jsonResponse($response);
    }

    /**
     * Récupère les données d'un widget spécifique
     * GET /api/widgets/{widget_name}
     *
     * @param array $params
     * @return void
     */
    public function show(array $params = []): void
    {
        $widgetName = $params['widget'] ?? null;

        if (!$widgetName) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Widget name is required'
            ], 400);
            return;
        }

        $widget = $this->registry->get($widgetName);

        if (!$widget) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Widget not found'
            ], 404);
            return;
        }

        // Vérifier les permissions
        $app = Application::getInstance();
        $user = $app->auth?->user();

        if (!$widget->canView($user)) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Permission denied',
                'required_permission' => $widget->getPermission()
            ], 403);
            return;
        }

        try {
            $data = $widget->getData();

            $response = [
                'success' => true,
                'widget' => $widgetName,
                'config' => $widget->getConfig(),
                'data' => $data
            ];

            $this->jsonResponse($response);
        } catch (\Exception $e) {
            error_log("Widget error: " . $e->getMessage());

            $this->jsonResponse([
                'success' => false,
                'error' => 'Widget data calculation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les données de plusieurs widgets en une seule requête
     * POST /api/widgets/batch
     * Body: {"widgets": ["widget1", "widget2", ...]}
     *
     * @return void
     */
    public function batch(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $widgetNames = $input['widgets'] ?? [];

        if (empty($widgetNames) || !is_array($widgetNames)) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Widgets array is required'
            ], 400);
            return;
        }

        $app = Application::getInstance();
        $user = $app->auth?->user();

        $results = [];
        $errors = [];

        foreach ($widgetNames as $widgetName) {
            $widget = $this->registry->get($widgetName);

            if (!$widget) {
                $errors[$widgetName] = 'Widget not found';
                continue;
            }

            if (!$widget->canView($user)) {
                $errors[$widgetName] = 'Permission denied';
                continue;
            }

            try {
                $results[$widgetName] = [
                    'config' => $widget->getConfig(),
                    'data' => $widget->getData()
                ];
            } catch (\Exception $e) {
                $errors[$widgetName] = 'Data calculation failed: ' . $e->getMessage();
            }
        }

        $response = [
            'success' => true,
            'data' => $results,
            'errors' => $errors,
            'count' => count($results)
        ];

        $this->jsonResponse($response);
    }

    /**
     * Invalide le cache d'un widget
     * POST /api/widgets/{widget_name}/clear-cache
     *
     * @param array $params
     * @return void
     */
    public function clearCache(array $params = []): void
    {
        $widgetName = $params['widget'] ?? null;

        if (!$widgetName) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Widget name is required'
            ], 400);
            return;
        }

        $widget = $this->registry->get($widgetName);

        if (!$widget) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Widget not found'
            ], 404);
            return;
        }

        // Vérifier les permissions (nécessite permission admin ou permission spécifique)
        $app = Application::getInstance();
        $user = $app->auth?->user();

        if (!$widget->canView($user)) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Permission denied'
            ], 403);
            return;
        }

        $cleared = $widget->clearCache();

        $response = [
            'success' => $cleared,
            'widget' => $widgetName,
            'message' => $cleared ? 'Cache cleared successfully' : 'Failed to clear cache'
        ];

        $this->jsonResponse($response, $cleared ? 200 : 500);
    }

    /**
     * Découvre automatiquement les widgets
     * POST /api/widgets/discover
     *
     * @return void
     */
    public function discover(): void
    {
        // TODO: Ajouter une permission admin pour cette action

        $discovered = $this->registry->discoverAllWidgets();

        $response = [
            'success' => true,
            'discovered' => $discovered,
            'total' => array_sum($discovered)
        ];

        $this->jsonResponse($response);
    }

    /**
     * Envoie une réponse JSON
     *
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
