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
            'users_count' => \Modules\Auth\Models\User::count(),
            'roles_count' => \Modules\RBAC\Models\Role::count(),
            'permissions_count' => \Modules\RBAC\Models\Permission::count(),
            'modules_count' => \Modules\RBAC\Models\Module::count(),
        ];

        // Récupérer les utilisateurs récents (5 derniers)
        $recent_users = \Modules\Auth\Models\User::orderBy('created_at', 'DESC')->limit(5)->get();

        echo view('backend.dashboard', [
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
