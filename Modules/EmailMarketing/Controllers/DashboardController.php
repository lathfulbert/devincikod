<?php

namespace Modules\EmailMarketing\Controllers;

use Modules\EmailMarketing\Models\EmailCampaign;
use Modules\EmailMarketing\Models\EmailMessage;
use Modules\EmailMarketing\Models\EmailTemplate;
use Modules\EmailMarketing\Models\Workflow;
use Modules\EmailMarketing\Services\CampaignAnalyticsService;

/**
 * Dashboard principal du module Email Marketing
 */
class DashboardController
{
    protected CampaignAnalyticsService $analyticsService;

    public function __construct()
    {
        $this->analyticsService = new CampaignAnalyticsService();
    }

    /**
     * Afficher le dashboard principal
     */
    public function index()
    {
        // Statistiques globales
        $globalStats = $this->analyticsService->getGlobalEmailStats([
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d')
        ]);

        // Compteurs
        $totalCampaigns = EmailCampaign::count();
        $activeCampaigns = EmailCampaign::where('status', 'sending')->count();
        $completedCampaigns = EmailCampaign::where('status', 'completed')->count();
        $totalTemplates = EmailTemplate::where('is_active', true)->count();
        $activeWorkflows = Workflow::where('status', 'active')->count();

        // Campagnes récentes
        $recentCampaigns = EmailCampaign::orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        // Top performing campaigns
        $topCampaigns = $this->analyticsService->getTopPerformingCampaigns(5, 'open_rate');

        // Messages récents
        $recentMessages = EmailMessage::orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        // Données pour graphiques
        $timeSeriesData = $this->getChartData();

        // Build stats array for view
        $stats = [
            'total_emails' => $totalCampaigns + count($recentMessages),
            'sent' => $globalStats['total_sent'] ?? 0,
            'open_rate' => $globalStats['rates']['open_rate'] ?? 0,
            'click_rate' => $globalStats['rates']['click_rate'] ?? 0
        ];

        return view('emailmarketing/dashboard', [
            'title' => 'Email Marketing Dashboard',
            'stats' => $stats,
            'globalStats' => $globalStats,
            'counters' => [
                'total_campaigns' => $totalCampaigns,
                'active_campaigns' => $activeCampaigns,
                'completed_campaigns' => $completedCampaigns,
                'total_templates' => $totalTemplates,
                'active_workflows' => $activeWorkflows
            ],
            'recentCampaigns' => $recentCampaigns,
            'topCampaigns' => $topCampaigns,
            'topPerformers' => $topCampaigns,
            'recentMessages' => $recentMessages,
            'chartData' => $timeSeriesData
        ]);
    }

    /**
    public function statistics()
    {
        // Statistiques par période
        $periods = [
            'today' => $this->getStatsForPeriod('today'),
            'week' => $this->getStatsForPeriod('week'),
            'month' => $this->getStatsForPeriod('month'),
            'year' => $this->getStatsForPeriod('year')
        ];

        // Statistiques par statut
        $byStatus = [
            'sent' => EmailMessage::where('status', 'sent')->count(),
            'delivered' => EmailMessage::where('status', 'delivered')->count(),
            'opened' => EmailMessage::where('status', 'opened')->count(),
            'clicked' => EmailMessage::where('status', 'clicked')->count(),
            'bounced' => EmailMessage::where('status', 'bounced')->count(),
            'failed' => EmailMessage::where('status', 'failed')->count()
        ];

        // Statistiques par gateway
        $byGateway = EmailMessage::selectRaw('gateway, COUNT(*) as count, SUM(cost) as total_cost')
            ->groupBy('gateway')
            ->get()
            ->toArray();

        // Évolution mensuelle
        $monthlyData = $this->getMonthlyEvolution();

        return view('emailmarketing/dashboard/statistics', [
            'title' => 'Email Marketing Statistics',
            'periods' => $periods,
            'byStatus' => $byStatus,
            'byGateway' => $byGateway,
            'monthlyData' => $monthlyData
        ]);
    }

    /**
     * Obtenir les statistiques pour une période
     */
    protected function getStatsForPeriod(string $period): array
    {
        $query = EmailMessage::query();

        switch ($period) {
            case 'today':
                $query->where('created_at', 'LIKE', date('Y-m-d') . '%');
                break;
            case 'week':
                $query->where('created_at', '>=', date('Y-m-d', strtotime('-7 days')));
                break;
            case 'month':
                $query->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')));
                break;
            case 'year':
                $query->where('created_at', '>=', date('Y-m-d', strtotime('-365 days')));
                break;
        }

        $messages = $query->get();

        return [
            'total' => count($messages),
            'sent' => count(array_filter($messages, fn($m) => $m->status !== 'pending')),
            'delivered' => count(array_filter($messages, fn($m) => $m->status === 'delivered')),
            'opened' => count(array_filter($messages, fn($m) => $m->status === 'opened')),
            'clicked' => count(array_filter($messages, fn($m) => $m->status === 'clicked')),
            'open_rate' => $this->calculateRate(
                count(array_filter($messages, fn($m) => $m->status === 'opened')),
                count(array_filter($messages, fn($m) => $m->status === 'delivered'))
            ),
            'click_rate' => $this->calculateRate(
                count(array_filter($messages, fn($m) => $m->status === 'clicked')),
                count(array_filter($messages, fn($m) => $m->status === 'delivered'))
            )
        ];
    }

    /**
     * Obtenir les données pour les graphiques
     */
    protected function getChartData(): array
    {
        $days = 30;
        $data = [
            'labels' => [],
            'sent' => [],
            'delivered' => [],
            'opened' => [],
            'clicked' => []
        ];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $data['labels'][] = date('M d', strtotime($date));

            $messages = EmailMessage::where('created_at', 'LIKE', $date . '%')->get();

            $data['sent'][] = count(array_filter($messages, fn($m) => $m->status !== 'pending'));
            $data['delivered'][] = count(array_filter($messages, fn($m) => $m->status === 'delivered'));
            $data['opened'][] = count(array_filter($messages, fn($m) => $m->status === 'opened'));
            $data['clicked'][] = count(array_filter($messages, fn($m) => $m->status === 'clicked'));
        }

        return $data;
    }

    /**
     * Obtenir l'évolution mensuelle
     */
    protected function getMonthlyEvolution(): array
    {
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthName = date('M Y', strtotime($month . '-01'));

            $messages = EmailMessage::where('created_at', 'LIKE', $month . '%')->get();

            $data[$monthName] = [
                'sent' => count(array_filter($messages, fn($m) => $m->status !== 'pending')),
                'delivered' => count(array_filter($messages, fn($m) => $m->status === 'delivered')),
                'opened' => count(array_filter($messages, fn($m) => $m->status === 'opened')),
                'clicked' => count(array_filter($messages, fn($m) => $m->status === 'clicked'))
            ];
        }

        return $data;
    }

    /**
     * Calculer un taux
     */
    protected function calculateRate(int $numerator, int $denominator): float
    {
        if ($denominator == 0) {
            return 0;
        }
        return round(($numerator / $denominator) * 100, 2);
    }
}
