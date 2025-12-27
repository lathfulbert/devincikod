<?php

namespace Modules\WhatsAppMarketing\Controllers;

use App\Core\Application;
use Modules\WhatsAppMarketing\Models\WhatsAppGateway;
use Modules\WhatsAppMarketing\Models\WhatsAppCampaign;
use Modules\WhatsAppMarketing\Models\WhatsAppMessage;

class WhatsAppDashboardController
{
    public function index()
    {
        $app = Application::getInstance();

        $stats = [
            'total_sent' => WhatsAppMessage::where('status', 'sent')->count(), // Placeholder logic
            'total_delivered' => WhatsAppMessage::where('status', 'delivered')->count(),
            'total_read' => WhatsAppMessage::where('status', 'read')->count(),
            'total_cost' => 0 // Need aggregation
        ];

        // Simple raw query for cost
        $db = \App\Core\Database\Database::getInstance();
        $costResult = $db->query("SELECT SUM(cost) as total FROM whatsapp_messages")->fetch();
        $stats['total_cost'] = $costResult['total'] ?? 0;

        $recentCampaigns = WhatsAppCampaign::query()->orderBy('created_at', 'desc')->limit(5)->get();
        $gateways = WhatsAppGateway::all();

        return view('WhatsAppMarketing/dashboard', [
            'title' => 'WhatsApp Marketing Dashboard',
            'stats' => $stats,
            'recentCampaigns' => $recentCampaigns,
            'gateways' => $gateways
        ]);
    }
}
