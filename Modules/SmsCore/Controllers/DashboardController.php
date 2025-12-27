<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use Modules\SmsCore\Models\SmsMessage;

class DashboardController
{
    public function index()
    {
        $app = Application::getInstance();

        $user = auth()->user();
        $userId = $user->id ?? ($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null);
        $canSeeAll = $user->can('sms.see_all');

        // Base query
        $baseQuery = \Modules\SmsCore\Models\SmsMessage::query();
        if (!$canSeeAll && $userId) {
            $baseQuery->where('user_id', $userId);
        }

        // Statistiques filtrées
        $totalMessages = (clone $baseQuery)->count();
        $messagesToday = (clone $baseQuery)->where('created_at', '>=', date('Y-m-d 00:00:00'))->count();
        $sentMessages = (clone $baseQuery)->where('status', 'sent')->count();
        $successRate = $totalMessages > 0 ? round(($sentMessages / $totalMessages) * 100, 1) : 0;

        // Messages récents filtrés
        $recentMessages = (clone $baseQuery)->orderBy('created_at', 'DESC')->limit(10)->get();

        // Coût total filtré
        $allMessages = (clone $baseQuery)->get();
        $totalCost = 0;
        foreach ($allMessages as $msg) {
            $totalCost += $msg->cost ?? 0.03;
        }

        // Get real wallet balance for current user
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
        $walletBalance = 0.00;

        if ($userId) {
            $wallet = \Modules\Wallet\Models\Wallet::where('user_id', $userId)->first();
            if ($wallet) {
                $walletBalance = $wallet->balance ?? 0.00;
            }
        }

        $stats = [
            'total_messages' => $totalMessages,
            'messages_today' => $messagesToday,
            'success_rate' => $successRate,
            'total_credits_used' => $totalCost,
            'active_gateways' => \Modules\Settings\Models\SmsGateway::where('is_active', 1)->count(),
            'wallet_balance' => $walletBalance
        ];

        return view('SmsCore/sms/dashboard', [
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

        // Get current user
        $currentUserId = $_SESSION['user']['id'] ?? null;
        $isAdmin = in_array($_SESSION['user']['role_id'] ?? 0, [1, 2]); // 1=admin, 2=owner

        // Build base query
        $messagesQuery = SmsMessage::where('created_at', '>=', $from . ' 00:00:00')
            ->where('created_at', '<=', $to . ' 23:59:59');

        // Filter by user if not admin
        if (!$isAdmin && $currentUserId) {
            $messagesQuery->where('user_id', $currentUserId);
        }

        // Get all messages in period
        $messages = $messagesQuery->orderBy('created_at', 'asc')->get();

        // Calculate statistics
        $totalSent = 0;
        $totalDelivered = 0;
        $totalFailed = 0;
        $totalPending = 0;

        foreach ($messages as $msg) {
            $status = strtolower($msg->status ?? '');
            if ($status === 'sent' || $status === 'delivered') {
                $totalSent++;
                if ($status === 'delivered') {
                    $totalDelivered++;
                }
            } elseif ($status === 'failed') {
                $totalFailed++;
            } else {
                $totalPending++;
            }
        }

        // Group by date for chart
        $dateData = [];
        foreach ($messages as $msg) {
            $date = date('Y-m-d', strtotime($msg->created_at));
            if (!isset($dateData[$date])) {
                $dateData[$date] = ['sent' => 0, 'delivered' => 0, 'failed' => 0];
            }

            $status = strtolower($msg->status ?? '');
            if ($status === 'sent' || $status === 'delivered') {
                $dateData[$date]['sent']++;
                if ($status === 'delivered') {
                    $dateData[$date]['delivered']++;
                }
            } elseif ($status === 'failed') {
                $dateData[$date]['failed']++;
            }
        }

        // Prepare chart data
        $chartLabels = [];
        $chartSent = [];
        $chartDelivered = [];
        $chartFailed = [];

        $currentDate = strtotime($from);
        $endDate = strtotime($to);
        while ($currentDate <= $endDate) {
            $dateKey = date('Y-m-d', $currentDate);
            $chartLabels[] = date('d/m', $currentDate);
            $chartSent[] = $dateData[$dateKey]['sent'] ?? 0;
            $chartDelivered[] = $dateData[$dateKey]['delivered'] ?? 0;
            $chartFailed[] = $dateData[$dateKey]['failed'] ?? 0;
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return view('SmsCore/sms/statistics', [
            'from' => $from,
            'to' => $to,
            'totalSent' => $totalSent,
            'totalDelivered' => $totalDelivered,
            'totalFailed' => $totalFailed,
            'totalPending' => $totalPending,
            'chartLabels' => json_encode($chartLabels),
            'chartSent' => json_encode($chartSent),
            'chartDelivered' => json_encode($chartDelivered),
            'chartFailed' => json_encode($chartFailed),
            'title' => 'SMS Statistics'
        ]);
    }
}
