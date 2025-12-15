<?php

namespace Modules\Users\Widgets;

use App\Core\Widget\AbstractWidget;
use Modules\Users\Models\User;

/**
 * Widget CreatedByUsers
 * Affiche le nombre d'utilisateurs créés par l'utilisateur connecté (owner)
 * ou tous les utilisateurs si admin/dashboard_view_all
 */
class CreatedByUsersWidget extends AbstractWidget
{
    protected string $name = 'users.created_by';
    protected string $type = 'stat';
    protected ?string $permission = null; // Contrôle dans canView
    protected bool $cacheable = false;
    protected int $cacheDuration = 300;

    protected array $config = [
        'title' => 'Utilisateurs créés',
        'description' => 'Utilisateurs créés par vous ou tous (admin)',
        'icon' => 'user-plus',
        'color' => 'info',
        'order' => 3
    ];

    protected function calculateData(): array
    {
        // Récupérer l'utilisateur courant depuis la session
        $user = null;
        if (isset($_SESSION['user']['id'])) {
            $user = User::find($_SESSION['user']['id']);
        }
        if (!$user) {
            return [
                'title' => $this->config['title'],
                'value' => 0,
                'description' => 'Non connecté',
                'icon' => $this->config['icon'],
                'trend' => null,
                'meta' => []
            ];
        }

        // Si admin ou dashboard_view_all, voir tous les users
        if ($user->hasRole('admin') || $user->can('dashboard_view_all')) {
            $count = User::count();
            $desc = 'Tous les utilisateurs';
        } else {
            $count = User::where('created_by', $user->id)->count();
            $desc = 'Créés par vous';
        }

        return [
            'title' => $this->config['title'],
            'value' => number_format($count, 0, ',', ' '),
            'description' => $desc,
            'icon' => $this->config['icon'],
            'trend' => null,
            'meta' => [
                'created_by' => $user->id,
                'is_admin' => $user->hasRole('admin'),
            ]
        ];
    }

    public function canView($user): bool
    {
        // Tout utilisateur connecté peut voir son propre widget
        return $user !== null;
    }
}
