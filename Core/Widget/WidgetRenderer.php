<?php

namespace App\Core\Widget;

use App\Core\Application;

/**
 * Classe WidgetRenderer
 *
 * Responsable du rendu des widgets dans les vues.
 * Fournit des méthodes helper pour intégrer facilement les widgets dans les templates.
 */
class WidgetRenderer
{
    private WidgetRegistry $registry;
    private ?object $user = null;

    public function __construct()
    {
        $this->registry = WidgetRegistry::getInstance();
        $app = Application::getInstance();
        $this->user = $app->auth?->user();
    }

    /**
     * Rend un widget unique
     *
     * @param string $widgetName Nom du widget
     * @param array $options Options de rendu
     * @return string HTML du widget
     */
    public function render(string $widgetName, array $options = []): string
    {
        $widget = $this->registry->get($widgetName);

        if (!$widget) {
            return $this->renderError("Widget '{$widgetName}' not found");
        }

        // Vérifier les permissions
        if (!$widget->canView($this->user)) {
            return $options['show_unauthorized'] ?? false
                ? $this->renderUnauthorized($widgetName)
                : '';
        }

        try {
            $data = $widget->getData();
            $config = $widget->getConfig();

            return $this->renderWidget($config, $data, $options);
        } catch (\Exception $e) {
            error_log("Widget render error: " . $e->getMessage());
            return $this->renderError("Failed to load widget data");
        }
    }

    /**
     * Rend plusieurs widgets d'un module
     *
     * @param string $module Nom du module
     * @param array $options Options de rendu
     * @return string HTML des widgets
     */
    public function renderModule(string $module, array $options = []): string
    {
        $widgets = $this->registry->getWidgetsForUser($this->user, $module);

        if (empty($widgets)) {
            return $options['show_empty'] ?? false
                ? $this->renderEmpty("No widgets available for module '{$module}'")
                : '';
        }

        $html = '';
        $columns = $options['columns'] ?? 3;
        $cardClass = $options['card_class'] ?? 'col-lg-' . (12 / $columns) . ' col-md-6 mb-4';

        foreach ($widgets as $widgetName => $widget) {
            try {
                $data = $widget->getData();
                $config = $widget->getConfig();

                $html .= '<div class="' . $cardClass . '">';
                $html .= $this->renderWidget($config, $data, $options);
                $html .= '</div>';
            } catch (\Exception $e) {
                error_log("Widget render error ({$widgetName}): " . $e->getMessage());
            }
        }

        return '<div class="row">' . $html . '</div>';
    }

    /**
     * Rend tous les widgets accessibles
     *
     * @param array $options Options de rendu
     * @return string HTML des widgets
     */
    public function renderAll(array $options = []): string
    {
        $widgets = $this->registry->getWidgetsForUser($this->user);

        if (empty($widgets)) {
            return $options['show_empty'] ?? false
                ? $this->renderEmpty("No widgets available")
                : '';
        }

        $html = '';
        $columns = $options['columns'] ?? 3;
        $cardClass = $options['card_class'] ?? 'col-lg-' . (12 / $columns) . ' col-md-6 mb-4';

        foreach ($widgets as $widgetName => $widget) {
            try {
                $data = $widget->getData();
                $config = $widget->getConfig();

                $html .= '<div class="' . $cardClass . '">';
                $html .= $this->renderWidget($config, $data, $options);
                $html .= '</div>';
            } catch (\Exception $e) {
                error_log("Widget render error ({$widgetName}): " . $e->getMessage());
            }
        }

        return '<div class="row">' . $html . '</div>';
    }

    /**
     * Génère le HTML d'un widget selon son type
     *
     * @param array $config Configuration du widget
     * @param array $data Données du widget
     * @param array $options Options de rendu
     * @return string HTML
     */
    private function renderWidget(array $config, array $data, array $options = []): string
    {
        $type = $config['type'] ?? 'stat';

        return match ($type) {
            'stat', 'kpi' => $this->renderStatWidget($data, $options),
            'chart' => $this->renderChartWidget($data, $options),
            'list' => $this->renderListWidget($data, $options),
            'info' => $this->renderInfoWidget($data, $options),
            'alert' => $this->renderAlertWidget($data, $options),
            'trend' => $this->renderTrendWidget($data, $options),
            'activity' => $this->renderActivityWidget($data, $options),
            default => $this->renderStatWidget($data, $options),
        };
    }

    /**
     * Rend un widget de type statistique
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    private function renderStatWidget(array $data, array $options = []): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $value = htmlspecialchars($data['value'] ?? '0');
        $description = htmlspecialchars($data['description'] ?? '');
        $icon = htmlspecialchars($data['icon'] ?? 'activity');
        $trend = $data['trend'] ?? ['percentage' => 0, 'direction' => 'stable'];

        $trendClass = match ($trend['direction']) {
            'up' => 'text-success',
            'down' => 'text-danger',
            default => 'text-muted',
        };

        $trendIcon = match ($trend['direction']) {
            'up' => 'arrow-up',
            'down' => 'arrow-down',
            default => 'minus',
        };

        $cardClass = $options['stat_card_class'] ?? 'card h-100';

        return <<<HTML
        <div class="{$cardClass}">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i data-feather="{$icon}" class="text-primary" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">{$title}</h6>
                        <h3 class="mb-0">{$value}</h3>
                    </div>
                </div>
                {$this->renderTrendBadge($trend, $trendClass, $trendIcon)}
                {$this->renderDescription($description)}
            </div>
        </div>
        HTML;
    }

    /**
     * Rend un widget de type trend
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    private function renderTrendWidget(array $data, array $options = []): string
    {
        // Pour l'instant, utiliser le même rendu que les stats
        return $this->renderStatWidget($data, $options);
    }

    /**
     * Rend un widget de type information
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    private function renderInfoWidget(array $data, array $options = []): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $value = htmlspecialchars($data['value'] ?? '');
        $description = htmlspecialchars($data['description'] ?? '');
        $icon = htmlspecialchars($data['icon'] ?? 'info');

        return <<<HTML
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i data-feather="{$icon}" class="text-info" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-title">{$title}</h6>
                        <p class="card-text">{$value}</p>
                        {$this->renderDescription($description)}
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    /**
     * Rend un widget de type alerte
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    private function renderAlertWidget(array $data, array $options = []): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $value = htmlspecialchars($data['value'] ?? '');
        $description = htmlspecialchars($data['description'] ?? '');
        $severity = $data['meta']['severity'] ?? 'info';

        $alertClass = match ($severity) {
            'danger', 'error', 'critical' => 'alert-danger',
            'warning' => 'alert-warning',
            'success' => 'alert-success',
            default => 'alert-info',
        };

        return <<<HTML
        <div class="card h-100">
            <div class="card-body">
                <div class="alert {$alertClass} mb-0" role="alert">
                    <h6 class="alert-heading">{$title}</h6>
                    <p class="mb-0">{$value}</p>
                    {$this->renderDescription($description)}
                </div>
            </div>
        </div>
        HTML;
    }

    /**
     * Rend un widget de type liste
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    private function renderListWidget(array $data, array $options = []): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $items = $data['meta']['items'] ?? [];

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemTitle = htmlspecialchars($item['title'] ?? '');
            $itemValue = htmlspecialchars($item['value'] ?? '');
            $itemsHtml .= <<<HTML
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {$itemTitle}
                <span class="badge bg-primary rounded-pill">{$itemValue}</span>
            </li>
            HTML;
        }

        return <<<HTML
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">{$title}</h6>
            </div>
            <ul class="list-group list-group-flush">
                {$itemsHtml}
            </ul>
        </div>
        HTML;
    }

    /**
     * Rend un widget de type activité
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    private function renderActivityWidget(array $data, array $options = []): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $activities = $data['meta']['activities'] ?? [];

        $activitiesHtml = '';
        foreach ($activities as $activity) {
            $activityTitle = htmlspecialchars($activity['title'] ?? '');
            $activityTime = htmlspecialchars($activity['time'] ?? '');
            $activityIcon = htmlspecialchars($activity['icon'] ?? 'activity');

            $activitiesHtml .= <<<HTML
            <div class="d-flex align-items-start mb-3">
                <div class="flex-shrink-0">
                    <i data-feather="{$activityIcon}" class="text-muted" style="width: 16px; height: 16px;"></i>
                </div>
                <div class="flex-grow-1 ms-2">
                    <p class="mb-0 small">{$activityTitle}</p>
                    <small class="text-muted">{$activityTime}</small>
                </div>
            </div>
            HTML;
        }

        return <<<HTML
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">{$title}</h6>
            </div>
            <div class="card-body">
                {$activitiesHtml}
            </div>
        </div>
        HTML;
    }

    /**
     * Rend un widget de type graphique (placeholder)
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    private function renderChartWidget(array $data, array $options = []): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $chartId = 'chart-' . uniqid();

        return <<<HTML
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">{$title}</h6>
            </div>
            <div class="card-body">
                <canvas id="{$chartId}"></canvas>
                <script>
                    // Placeholder pour intégration Chart.js ou autre librairie
                    console.log('Chart widget data:', {$this->jsonEncode($data)});
                </script>
            </div>
        </div>
        HTML;
    }

    /**
     * Rend le badge de tendance
     *
     * @param array $trend
     * @param string $trendClass
     * @param string $trendIcon
     * @return string
     */
    private function renderTrendBadge(array $trend, string $trendClass, string $trendIcon): string
    {
        if ($trend['percentage'] == 0) {
            return '';
        }

        $percentage = abs($trend['percentage']);

        return <<<HTML
        <div class="mt-2">
            <span class="{$trendClass} small">
                <i data-feather="{$trendIcon}" style="width: 12px; height: 12px;"></i>
                {$percentage}%
            </span>
        </div>
        HTML;
    }

    /**
     * Rend la description si présente
     *
     * @param string $description
     * @return string
     */
    private function renderDescription(string $description): string
    {
        if (empty($description)) {
            return '';
        }

        return '<p class="text-muted small mb-0 mt-2">' . $description . '</p>';
    }

    /**
     * Rend un message d'erreur
     *
     * @param string $message
     * @return string
     */
    private function renderError(string $message): string
    {
        return <<<HTML
        <div class="alert alert-danger" role="alert">
            <i data-feather="alert-circle"></i> {$message}
        </div>
        HTML;
    }

    /**
     * Rend un message "non autorisé"
     *
     * @param string $widgetName
     * @return string
     */
    private function renderUnauthorized(string $widgetName): string
    {
        return <<<HTML
        <div class="alert alert-warning" role="alert">
            <i data-feather="lock"></i> Vous n'avez pas les permissions pour afficher le widget '{$widgetName}'
        </div>
        HTML;
    }

    /**
     * Rend un message "vide"
     *
     * @param string $message
     * @return string
     */
    private function renderEmpty(string $message): string
    {
        return <<<HTML
        <div class="alert alert-info" role="alert">
            <i data-feather="info"></i> {$message}
        </div>
        HTML;
    }

    /**
     * Encode en JSON de manière sûre pour JavaScript
     *
     * @param mixed $data
     * @return string
     */
    private function jsonEncode($data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
