<?php

namespace Modules\Dashboard\Controllers;

use App\Core\Application;
use Modules\RBAC\Models\Module as ModuleModel;

class DashboardController
{
    public function index()
    {
        // Vérification d'accès
        if (!auth()->check()) {
            http_response_code(403);
            echo view('errors.403', [
                'message' => "Vous devez être connecté pour accéder au dashboard.",
                'required_permission' => 'auth'
            ]);
            return;
        }

        $user = auth()->user();
        $isAdmin = false;
        if (is_object($user) && method_exists($user, 'hasRole')) {
            $isAdmin = $user->hasRole('admin');
        } elseif (is_array($user) && isset($user['role']) && $user['role'] === 'admin') {
            $isAdmin = true;
        }

        if (!$isAdmin && (!is_object($user) || !method_exists($user, 'can') || !$user->can('access.admin'))) {
            http_response_code(403);
            echo view('errors.403', [
                'message' => "Vous n'avez pas l'autorisation d'accéder au dashboard.",
                'required_permission' => 'access.admin'
            ]);
            return;
        }

        // Récupérer les modules actifs
        $activeModules = ModuleModel::where('is_active', 1)->get();

        // Préparer les données pour la vue
        $data = [
            'title' => 'Dashboard',
            'active_modules' => $activeModules,
            'user' => $user,
            'is_admin' => $isAdmin
        ];

        echo view('backend.dashboard', $data);
    }
}