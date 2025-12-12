<?php

namespace Modules\SmsCore\Widgets;

use App\Core\Widget\AbstractWidget;
use App\Core\Database\Database;

/**
 * Widget TotalSms
 *
 * Affiche le nombre total de SMS envoyés
 *
 * Type: Statistique / KPI
 * Permission: sms.view ou admin.access
 */
class TotalSmsWidget extends AbstractWidget
{
    protected string $name = 'sms.total';
    protected string $type = 'stat';
    protected ?string $permission = 'sms.view';
    protected bool $cacheable = true;
    protected int $cacheDuration = 300; // 5 minutes

    protected array $config = [
        'title' => 'SMS envoyés',
        'description' => 'Nombre total de SMS envoyés depuis le début',
        'icon' => 'send',
        'color' => 'primary',
        'order' => 1
    ];

    protected function calculateData(): array
    {
        $db = Database::getInstance();

        // Total SMS
        $result = $db->query("SELECT COUNT(*) as total FROM sms_messages")->fetch();
        $totalSms = $result['total'] ?? 0;

        // SMS du mois en cours
        $currentMonth = date('Y-m-01 00:00:00');
        $stmt = $db->query(
            "SELECT COUNT(*) as total FROM sms_messages WHERE created_at >= ?",
            [$currentMonth]
        );
        $thisMonthSms = $stmt->fetch()['total'] ?? 0;

        // SMS du mois précédent
        $previousMonth = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $currentMonthStart = date('Y-m-01 00:00:00');
        $stmt = $db->query(
            "SELECT COUNT(*) as total FROM sms_messages WHERE created_at >= ? AND created_at < ?",
            [$previousMonth, $currentMonthStart]
        );
        $lastMonthSms = $stmt->fetch()['total'] ?? 0;

        // Calculer la tendance
        $trend = $this->calculateTrend($thisMonthSms, $lastMonthSms);

        return [
            'title' => 'SMS envoyés',
            'value' => number_format($totalSms, 0, ',', ' '),
            'description' => number_format($thisMonthSms, 0, ',', ' ') . ' ce mois',
            'icon' => 'send',
            'trend' => $trend,
            'meta' => [
                'total' => $totalSms,
                'this_month' => $thisMonthSms,
                'last_month' => $lastMonthSms,
                'url' => '/sms/campaigns'
            ]
        ];
    }

    private function calculateTrend(int $current, int $previous): array
    {
        if ($previous == 0) {
            return [
                'percentage' => 0,
                'direction' => 'stable'
            ];
        }

        $percentage = (($current - $previous) / $previous) * 100;
        $direction = $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'stable');

        return [
            'percentage' => round(abs($percentage), 1),
            'direction' => $direction
        ];
    }
}
