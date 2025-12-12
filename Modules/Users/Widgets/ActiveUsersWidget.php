<?php

namespace Modules\Users\Widgets;

use App\Core\Widget\AbstractWidget;
use Modules\Users\Models\User;

/**
 * Widget ActiveUsers
 *
 * Affiche le nombre d'utilisateurs actifs
 *
 * Type: Statistique / KPI
 * Permission: users.view
 */
class ActiveUsersWidget extends AbstractWidget
{
    protected string $name = 'users.active';
    protected string $type = 'stat';
    protected ?string $permission = 'users.view';
    protected bool $cacheable = true;
    protected int $cacheDuration = 300; // 5 minutes

    protected array $config = [
        'title' => 'Utilisateurs actifs',
        'description' => 'Utilisateurs avec statut actif',
        'icon' => 'user-check',
        'color' => 'success',
        'order' => 2
    ];

    protected function calculateData(): array
    {
        // Utilisateurs actifs
        $activeUsers = User::where('is_active', 1)->count();
        $totalUsers = User::count();

        // Calculer le pourcentage
        $percentage = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;

        return [
            'title' => 'Utilisateurs actifs',
            'value' => number_format($activeUsers, 0, ',', ' '),
            'description' => round($percentage, 1) . '% des utilisateurs',
            'icon' => 'user-check',
            'trend' => [
                'percentage' => round($percentage, 1),
                'direction' => $percentage >= 80 ? 'up' : ($percentage < 50 ? 'down' : 'stable')
            ],
            'meta' => [
                'active' => $activeUsers,
                'total' => $totalUsers,
                'percentage' => $percentage,
                'inactive' => $totalUsers - $activeUsers
            ]
        ];
    }
}
