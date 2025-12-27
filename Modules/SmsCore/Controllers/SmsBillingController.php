<?php

namespace Modules\SmsCore\Controllers;


use App\Core\Application;
use Modules\SmsCore\Models\SmsBillingLog;

class SmsBillingController
{
    /**
     * Affiche la facturation SMS
     */
    public function index()
    {
        $app = Application::getInstance();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $query = SmsBillingLog::orderBy('created_at', 'desc');

        // Filtres éventuels (statut, user, etc.)
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $query->where('status', $_GET['status']);
        }
        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
            $query->where('user_id', $_GET['user_id']);
        }

        $total = $query->count();
        $logs = $query->with('user')->limit($limit)->offset($offset)->get();
        $totalPages = ceil($total / $limit);

        return view('SmsCore/sms/billing/index', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $total
        ]);
    }
}
