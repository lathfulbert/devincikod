<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class DashboardController
{
    public function index()
    {
        $app = Application::getInstance();

        // Get real statistics from database
        $totalMessages = \Modules\SmsCore\Models\SmsMessage::count();

        $messagesToday = \Modules\SmsCore\Models\SmsMessage::where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->count();

        $sentMessages = \Modules\SmsCore\Models\SmsMessage::where('status', 'sent')->count();
        $successRate = $totalMessages > 0 ? round(($sentMessages / $totalMessages) * 100, 1) : 0;

        // Get recent messages
        $recentMessages = \Modules\SmsCore\Models\SmsMessage::orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        // Calculate total cost manually (QueryBuilder doesn't have sum())
        $allMessages = \Modules\SmsCore\Models\SmsMessage::all();
        $totalCost = 0;
        foreach ($allMessages as $msg) {
            $totalCost += $msg->cost ?? 0.03; // Default 0.03 if cost not set
        }

        // Wallet balance (você pode adaptar conforme seu sistema)
        $walletBalance = 500.00; // TODO: Implementar sistema de wallet real

        $stats = [
            'total_messages' => $totalMessages,
            'messages_today' => $messagesToday,
            'success_rate' => $successRate,
            'total_credits_used' => $totalCost,
            'active_gateways' => \Modules\Settings\Models\SmsGateway::where('is_active', 1)->count(),
            'wallet_balance' => $walletBalance
        ];

        echo view('smscore/sms/dashboard', [
            'stats' => $stats,
            'recentMessages' => $recentMessages,
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

        echo view('smscore/sms/statistics', [
            'chartData' => $chartData,
            'from' => $from,
            'to' => $to,
            'title' => 'SMS Statistics'
        ]);
    }
}
