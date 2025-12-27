<?php

namespace Modules\Auth\Controllers;

use Modules\Auth\Models\AuthLog;

/**
 * Auth Logs Controller
 * Display authentication audit logs
 */
class AuthLogsController
{
    /**
     * Show auth logs
     */
    public function index()
    {
        // Get recent logs (paginated)
        $perPage = 50;
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        $logs = AuthLog::query()
            ->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        // Get total count for pagination
        $total = AuthLog::query()->count();
        $totalPages = ceil($total / $perPage);

        return view('auth/logs/index', [
            'title' => 'Journal d\'authentification',
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Get logs for specific user
     */
    public function userLogs()
    {
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['flash_error'] = 'User ID required';
            redirect('/admin/auth/logs');
            return;
        }

        $logs = AuthLog::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get();

        return view('auth/logs/index', [
            'title' => 'Journal d\'authentification - Utilisateur #' . $userId,
            'logs' => $logs,
            'page' => 1,
            'totalPages' => 1
        ]);
    }
}
