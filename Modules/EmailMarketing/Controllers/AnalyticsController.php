<?php

namespace Modules\EmailMarketing\Controllers;

use Modules\EmailMarketing\Services\CampaignAnalyticsService;
use Modules\EmailMarketing\Services\MultiChannelService;
use Modules\EmailMarketing\Models\EmailCampaign;

/**
 * Controller pour les analytics et reporting
 */
class AnalyticsController
{
    protected CampaignAnalyticsService $analyticsService;
    protected MultiChannelService $multiChannelService;

    public function __construct()
    {
        $this->analyticsService = new CampaignAnalyticsService();
        // MultiChannelService pour comparaison canaux
    }

    /**
     * Page analytics principale
     */
    public function index()
    {
        // Statistiques globales
        $globalStats = $this->analyticsService->getGlobalEmailStats([
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d')
        ]);

        // Top performing campaigns
        $topByOpenRate = $this->analyticsService->getTopPerformingCampaigns(10, 'open_rate');
        $topByClickRate = $this->analyticsService->getTopPerformingCampaigns(10, 'click_rate');

        // Liste des campagnes pour sélection
        $campaigns = EmailCampaign::where('status', 'completed')
            ->orderBy('completed_at', 'DESC')
            ->limit(50)
            ->get();

        echo view('emailmarketing/analytics/index', [
            'title' => 'Email Marketing Analytics',
            'globalStats' => $globalStats,
            'topByOpenRate' => $topByOpenRate,
            'topByClickRate' => $topByClickRate,
            'campaigns' => $campaigns
        ]);
    }

    /**
     * Analytics d'une campagne spécifique
     */
    public function campaign()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/analytics');
            exit;
        }

        $stats = $this->analyticsService->getEmailCampaignStats($campaign->id);
        $timeSeriesData = $this->analyticsService->getTimeSeriesData($campaign->id, 'hour');

        echo view('emailmarketing/analytics/campaign', [
            'title' => 'Campaign Analytics: ' . $campaign->name,
            'campaign' => $campaign,
            'stats' => $stats,
            'timeSeriesData' => $timeSeriesData
        ]);
    }

    /**
     * Comparer plusieurs campagnes
     */
    public function compare()
    {
        $campaignIds = $_GET['campaign_ids'] ?? [];

        if (is_string($campaignIds)) {
            $campaignIds = explode(',', $campaignIds);
        }

        if (empty($campaignIds)) {
            $_SESSION['flash']['danger'][] = 'Please select campaigns to compare';
            redirect('/admin/email-marketing/analytics');
            exit;
        }

        $comparisons = $this->analyticsService->compareCampaigns($campaignIds);

        // Charger les campagnes complètes
        $campaigns = [];
        foreach ($campaignIds as $id) {
            $campaign = EmailCampaign::find($id);
            if ($campaign) {
                $campaigns[] = $campaign;
            }
        }

        echo view('emailmarketing/analytics/compare', [
            'title' => 'Compare Campaigns',
            'campaigns' => $campaigns,
            'comparisons' => $comparisons
        ]);
    }

    /**
     * Analytics multicanal (Email + SMS)
     */
    public function multichannel()
    {
        $id = $_GET['id'] ?? null;

        // Récupérer les stats multicanal depuis campaign_logs
        $stats = $this->analyticsService->getMultiChannelCampaignStats($id, 'multichannel');

        echo view('emailmarketing/analytics/multichannel', [
            'title' => 'Multichannel Campaign Analytics',
            'campaignId' => $id,
            'stats' => $stats
        ]);
    }
}
