<?php

namespace Modules\SmsCore\Widgets;

use App\Core\Widget\AbstractWidget;

/**
 * Widget SMS Statistics
 *
 * Affiche les statistiques des SMS envoyés
 *
 * Type: stat
 * Permission: sms.view ou access.sms_core
 */
class SmsStatisticsWidget extends AbstractWidget
{
    protected string $name = 'sms.statistics';
    protected string $type = 'stat';
    protected ?string $permission = 'access.sms_core'; // Nécessite l'accès au module SMS
    protected bool $cacheable = true;
    protected int $cacheTtl = 300; // 5 minutes

    protected array $config = [
        'title' => 'Statistiques SMS',
        'description' => 'Aperçu des SMS envoyés',
        'icon' => 'message-circle',
        'color' => 'info',
        'order' => 10
    ];

    protected function calculateData(): array
    {
        // Simuler des données pour l'exemple - à remplacer par de vraies requêtes DB
        $totalSent = $this->getTotalSmsSent();
        $totalDelivered = $this->getTotalSmsDelivered();
        $totalFailed = $this->getTotalSmsFailed();
        $todaySent = $this->getTodaySmsSent();

        return [
            'total_sent' => $totalSent,
            'total_delivered' => $totalDelivered,
            'total_failed' => $totalFailed,
            'today_sent' => $todaySent,
            'delivery_rate' => $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 1) : 0,
            'stats' => [
                [
                    'label' => 'Total envoyés',
                    'value' => number_format($totalSent),
                    'icon' => 'send',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Livrés aujourd' . "'" . 'hui',
                    'value' => number_format($todaySent),
                    'icon' => 'check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Taux de livraison',
                    'value' => $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 1) . '%' : '0%',
                    'icon' => 'trending-up',
                    'color' => 'info'
                ]
            ]
        ];
    }

    private function getTotalSmsSent(): int
    {
        // TODO: Remplacer par une vraie requête DB
        // Exemple: return DB::table('sms_messages')->count();
        return rand(1000, 5000); // Simulation
    }

    private function getTotalSmsDelivered(): int
    {
        // TODO: Remplacer par une vraie requête DB
        return rand(800, 4500); // Simulation
    }

    private function getTotalSmsFailed(): int
    {
        // TODO: Remplacer par une vraie requête DB
        return rand(50, 500); // Simulation
    }

    private function getTodaySmsSent(): int
    {
        // TODO: Remplacer par une vraie requête DB
        return rand(50, 200); // Simulation
    }
}