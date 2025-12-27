<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;

class AdminController
{
    public function index()
    {
        $app = Application::getInstance();

        // Récupérer les statistiques
        $stats = [
            'users_count' => \Modules\Users\Models\User::count(),
            'roles_count' => \Modules\RBAC\Models\Role::count(),
            'permissions_count' => \Modules\RBAC\Models\Permission::count(),
            // 'modules_count' => \Modules\RBAC\Models\Module::count(), // Module model might not exist yet or needs check
        ];

        // Récupérer les utilisateurs récents (5 derniers)
        $recent_users = \Modules\Users\Models\User::orderBy('created_at', 'DESC')->limit(5)->get();

        return view('backend.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recent_users' => $recent_users
        ]);
    }

    public function dashboard()
    {
        return $this->index();
    }
}
