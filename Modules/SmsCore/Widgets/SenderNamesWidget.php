<?php

namespace Modules\SmsCore\Widgets;

use App\Core\Widget\AbstractWidget;
use App\Core\Database\Database;

/**
 * Widget SenderNames
 *
 * Affiche les noms d'expéditeur disponibles
 *
 * Type: Information / KPI
 * Permission: sms.sender_names.view
 */
class SenderNamesWidget extends AbstractWidget
{
    protected string $name = 'sms.sender_names';
    protected string $type = 'stat';
    protected ?string $permission = 'sms.sender_names.view';
    protected bool $cacheable = true;
    protected int $cacheDuration = 600; // 10 minutes

    protected array $config = [
        'title' => 'Noms d\'expéditeur',
        'description' => 'Nombre de sender names configurés',
        'icon' => 'tag',
        'color' => 'info',
        'order' => 3
    ];

    protected function calculateData(): array
    {
        $db = Database::getInstance();

        // Total sender names
        $result = $db->query("SELECT COUNT(*) as total FROM sms_sender_names")->fetch();
        $totalSenders = $result['total'] ?? 0;

        // Sender names actifs
        $result = $db->query("SELECT COUNT(*) as total FROM sms_sender_names WHERE is_active = 1")->fetch();
        $activeSenders = $result['total'] ?? 0;

        return [
            'title' => 'Noms d\'expéditeur',
            'value' => number_format($activeSenders, 0, ',', ' '),
            'description' => "{$activeSenders} actifs sur {$totalSenders} total",
            'icon' => 'tag',
            'trend' => [
                'percentage' => 0,
                'direction' => 'stable'
            ],
            'meta' => [
                'total' => $totalSenders,
                'active' => $activeSenders,
                'inactive' => $totalSenders - $activeSenders,
                'url' => '/sms/sender-names'
            ]
        ];
    }
}
