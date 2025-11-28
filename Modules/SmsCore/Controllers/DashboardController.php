<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class DashboardController
{
    public function index()
    {
        $app = Application::getInstance();

        // Mock statistics for now
        $stats = [
            'total_messages' => 1250,
            'messages_today' => 85,
            'success_rate' => 98.5,
            'total_credits_used' => 156.75,
            'active_gateways' => 2,
            'wallet_balance' => 500.00
        ];

        echo $app->view->render('backend/sms/dashboard', [
            'stats' => $stats,
            'title' => 'SMS Dashboard'
        ]);
    }

    public function statistics()
    {
        $app = Application::getInstance();

        // Get date range from request
        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to = $_GET['to'] ?? date('Y-m-d');

        // Mock chart data
        $chartData = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'sent' => [120, 150, 180, 145, 200, 95, 110],
            'delivered' => [115, 145, 175, 140, 195, 90, 105],
            'failed' => [5, 5, 5, 5, 5, 5, 5]
        ];

        echo $app->view->render('backend/sms/statistics', [
            'chartData' => $chartData,
            'from' => $from,
            'to' => $to,
            'title' => 'SMS Statistics'
        ]);
    }
}
