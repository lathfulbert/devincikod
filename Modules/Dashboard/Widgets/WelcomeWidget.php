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
        $user = function_exists('auth') ? auth()->user() : null;
        $userName = $user ? ($user->first_name ?? $user->username ?? 'Utilisateur') : 'Visiteur';

        // Compter les modules actifs
        $app = \App\Core\Application::getInstance();
        $activeModules = count($app->moduleManager->getModules());

        // Compter les utilisateurs actifs (simulation)
        $activeUsers = $this->getActiveUsersCount();

        return [
            'title' => 'Bienvenue ' . $userName,
            'value' => 'Dashboard des modules actifs',
            'description' => "Vous avez accès à {$activeModules} modules actifs. {$activeUsers} utilisateurs actifs aujourd'hui.",
            'icon' => 'home',
            'meta' => [
                'welcome_message' => 'Tous vos widgets sont affichés ci-dessous.',
                'modules_count' => $activeModules,
                'active_users' => $activeUsers
            ]
        ];
    }

    private function getActiveUsersCount(): int
    {
        // TODO: Remplacer par une vraie requête DB
        // Exemple: return DB::table('users')->where('last_login', '>', now()->subDay())->count();
        return rand(10, 100); // Simulation
    }
}