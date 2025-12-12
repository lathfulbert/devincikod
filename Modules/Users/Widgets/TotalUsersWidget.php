<?php

namespace Modules\Users\Widgets;

use App\Core\Widget\AbstractWidget;
use Modules\Users\Models\User;

/**
 * Widget TotalUsers
 *
 * Affiche le nombre total d'utilisateurs et l'évolution
 *
 * Type: Statistique / KPI
 * Permission: users.view ou admin.access
 */
class TotalUsersWidget extends AbstractWidget
{
    protected string $name = 'users.total';
    protected string $type = 'stat';
    protected ?string $permission = 'users.view';
    protected bool $cacheable = true;
    protected int $cacheDuration = 600; // 10 minutes

    protected array $config = [
        'title' => 'Utilisateurs totaux',
        'description' => 'Nombre total d\'utilisateurs enregistrés dans le système',
        'icon' => 'users',
        'color' => 'primary',
        'order' => 1
    ];

    /**
     * Calcule les données du widget
     *
     * @return array
     */
    protected function calculateData(): array
    {
        // Nombre total d'utilisateurs
        $totalUsers = User::count();

        // Calculer l'évolution (nouveaux utilisateurs dans les 30 derniers jours)
        $lastMonthUsers = User::where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))->count();
        $previousMonthUsers = User::where('created_at', '<', date('Y-m-d', strtotime('-30 days')))
            ->where('created_at', '>=', date('Y-m-d', strtotime('-60 days')))
            ->count();

        // Calculer la tendance
        $trend = $this->calculateTrend($lastMonthUsers, $previousMonthUsers);

        return [
            'title' => 'Utilisateurs totaux',
            'value' => number_format($totalUsers, 0, ',', ' '),
            'description' => "dont {$lastMonthUsers} nouveaux ce mois",
            'icon' => 'users',
            'trend' => $trend,
            'meta' => [
                'total' => $totalUsers,
                'last_month' => $lastMonthUsers,
                'previous_month' => $previousMonthUsers,
                'url' => '/admin/users'
            ]
        ];
    }

    /**
     * Calcule la tendance entre deux périodes
     *
     * @param int $current
     * @param int $previous
     * @return array
     */
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
