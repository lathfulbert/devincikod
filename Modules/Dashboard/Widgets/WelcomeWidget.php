<?php

namespace Modules\Dashboard\Widgets;

use App\Core\Widget\AbstractWidget;

/**
 * Widget Welcome
 *
 * Affiche un message de bienvenue sur le dashboard
 *
 * Type: info
 * Permission: null (accessible à tous)
 */
class WelcomeWidget extends AbstractWidget
{
    protected string $name = 'dashboard.welcome';
    protected string $type = 'info';
    protected ?string $permission = null; // Accessible à tous
    protected bool $cacheable = false; // Pas de cache pour ce widget

    protected array $config = [
        'title' => 'Bienvenue',
        'description' => 'Message de bienvenue sur le dashboard',
        'icon' => 'home',
        'color' => 'primary',
        'order' => 1
    ];

    protected function calculateData(): array
    {
        $user = $this->getCurrentUser();
        $userName = $user ? ($user->first_name ?? $user->username ?? 'Utilisateur') : 'Visiteur';

        return [
            'title' => 'Bienvenue ' . $userName,
            'value' => 'Dashboard des modules actifs',
            'description' => 'Voici un aperçu des statistiques de vos modules actifs.',
            'icon' => 'home',
            'meta' => [
                'welcome_message' => 'Tous vos widgets sont affichés ci-dessous.'
            ]
        ];
    }
}