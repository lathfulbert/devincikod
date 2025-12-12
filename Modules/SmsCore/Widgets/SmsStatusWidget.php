<?php

namespace Modules\SmsCore\Widgets;

use App\Core\Widget\AbstractWidget;
use App\Core\Database\Database;

/**
 * Widget SmsStatus
 *
 * Affiche le statut des SMS (succès, échec, en attente)
 *
 * Type: Liste / Statistique
 * Permission: sms.view
 */
class SmsStatusWidget extends AbstractWidget
{
    protected string $name = 'sms.status';
    protected string $type = 'list';
    protected ?string $permission = 'sms.view';
    protected bool $cacheable = true;
    protected int $cacheDuration = 300; // 5 minutes

    protected array $config = [
        'title' => 'Statut des SMS',
        'description' => 'Répartition des SMS par statut',
        'icon' => 'pie-chart',
        'color' => 'info',
        'order' => 2
    ];

    protected function calculateData(): array
    {
        $db = Database::getInstance();

        // Compter par statut
        $result = $db->query("
            SELECT
                status,
                COUNT(*) as count
            FROM sms_messages
            GROUP BY status
        ")->fetchAll();

        $statusCounts = [
            'sent' => 0,
            'pending' => 0,
            'failed' => 0,
            'delivered' => 0
        ];

        foreach ($result as $row) {
            $statusCounts[$row['status']] = (int)$row['count'];
        }

        $total = array_sum($statusCounts);

        // Calculer le taux de succès
        $successRate = $total > 0
            ? (($statusCounts['sent'] + $statusCounts['delivered']) / $total) * 100
            : 0;

        $items = [
            [
                'title' => 'Envoyés',
                'value' => number_format($statusCounts['sent'], 0, ',', ' '),
                'icon' => 'check-circle',
                'color' => 'success'
            ],
            [
                'title' => 'Livrés',
                'value' => number_format($statusCounts['delivered'], 0, ',', ' '),
                'icon' => 'check',
                'color' => 'success'
            ],
            [
                'title' => 'En attente',
                'value' => number_format($statusCounts['pending'], 0, ',', ' '),
                'icon' => 'clock',
                'color' => 'warning'
            ],
            [
                'title' => 'Échecs',
                'value' => number_format($statusCounts['failed'], 0, ',', ' '),
                'icon' => 'x-circle',
                'color' => 'danger'
            ]
        ];

        return [
            'title' => 'Statut des SMS',
            'value' => round($successRate, 1) . '% de succès',
            'description' => 'Répartition par statut',
            'icon' => 'pie-chart',
            'trend' => [
                'percentage' => round($successRate, 1),
                'direction' => $successRate >= 95 ? 'up' : ($successRate < 80 ? 'down' : 'stable')
            ],
            'meta' => [
                'items' => $items,
                'total' => $total,
                'success_rate' => $successRate,
                'counts' => $statusCounts
            ]
        ];
    }
}
