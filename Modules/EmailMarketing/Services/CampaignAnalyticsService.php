<?php

namespace Modules\EmailMarketing\Services;

use Modules\EmailMarketing\Models\EmailCampaign;
use Modules\EmailMarketing\Models\EmailMessage;
use Modules\EmailMarketing\Models\EmailLog;
use Modules\EmailMarketing\Models\CampaignLog;

/**
 * Service d'analytics pour les campagnes email et multicanal
 */
class CampaignAnalyticsService
{
    /**
     * Obtenir les statistiques globales email
     */
    public function getGlobalEmailStats(array $filters = []): array
    {
        $query = EmailMessage::query();

        // Filtres de date
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        $messages = $query->get();

        return [
            'total_sent' => count(array_filter($messages, fn($m) => $m->status !== 'pending')),
            'total_delivered' => count(array_filter($messages, fn($m) => $m->status === 'delivered')),
            'total_opened' => count(array_filter($messages, fn($m) => $m->status === 'opened')),
            'total_clicked' => count(array_filter($messages, fn($m) => $m->status === 'clicked')),
            'total_bounced' => count(array_filter($messages, fn($m) => $m->status === 'bounced')),
            'total_failed' => count(array_filter($messages, fn($m) => $m->status === 'failed')),

            'rates' => [
                'delivery_rate' => $this->calculateRate(
                    count(array_filter($messages, fn($m) => $m->status === 'delivered')),
                    count(array_filter($messages, fn($m) => $m->status !== 'pending'))
                ),
                'open_rate' => $this->calculateRate(
                    count(array_filter($messages, fn($m) => $m->status === 'opened')),
                    count(array_filter($messages, fn($m) => $m->status === 'delivered'))
                ),
                'click_rate' => $this->calculateRate(
                    count(array_filter($messages, fn($m) => $m->status === 'clicked')),
                    count(array_filter($messages, fn($m) => $m->status === 'delivered'))
                ),
                'bounce_rate' => $this->calculateRate(
                    count(array_filter($messages, fn($m) => $m->status === 'bounced')),
                    count(array_filter($messages, fn($m) => $m->status !== 'pending'))
                )
            ],

            'total_cost' => array_sum(array_map(fn($m) => $m->cost ?? 0, $messages))
        ];
    }

    /**
     * Obtenir les statistiques d'une campagne email
     */
    public function getEmailCampaignStats(int $campaignId): array
    {
        $campaign = EmailCampaign::find($campaignId);

        if (!$campaign) {
            return ['error' => 'Campaign not found'];
        }

        $messages = EmailMessage::where('campaign_id', $campaignId)->get();

        return [
            'campaign' => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'created_at' => $campaign->created_at,
                'started_at' => $campaign->started_at,
                'completed_at' => $campaign->completed_at
            ],
            'counts' => [
                'total_recipients' => $campaign->total_recipients,
                'sent' => $campaign->sent_count,
                'delivered' => $campaign->delivered_count,
                'opened' => $campaign->opened_count,
                'clicked' => $campaign->clicked_count,
                'bounced' => $campaign->bounced_count,
                'failed' => $campaign->failed_count,
                'unsubscribed' => $campaign->unsubscribed_count
            ],
            'rates' => [
                'progress' => $campaign->getProgress(),
                'delivery_rate' => $campaign->sent_count > 0
                    ? $this->calculateRate($campaign->delivered_count, $campaign->sent_count)
                    : 0,
                'open_rate' => $campaign->getOpenRate(),
                'click_rate' => $campaign->getClickRate(),
                'bounce_rate' => $campaign->getBounceRate(),
                'unsubscribe_rate' => $campaign->sent_count > 0
                    ? $this->calculateRate($campaign->unsubscribed_count, $campaign->sent_count)
                    : 0
            ],
            'timeline' => $this->getCampaignTimeline($campaignId),
            'top_links' => $this->getTopClickedLinks($campaignId),
            'geographic' => $this->getGeographicDistribution($campaignId)
        ];
    }

    /**
     * Obtenir les statistiques d'une campagne multicanal (Email + SMS)
     */
    public function getMultiChannelCampaignStats(int $campaignId, string $campaignType = 'multichannel'): array
    {
        $stats = CampaignLog::getStatsByCampaign($campaignId);

        return [
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignType,
            'global' => [
                'total' => $stats['total'],
                'by_channel' => $stats['by_channel'],
                'by_status' => $stats['by_status'],
                'total_cost' => $stats['total_cost']
            ],
            'email' => $this->getChannelDetails($campaignId, 'email'),
            'sms' => $this->getChannelDetails($campaignId, 'sms'),
            'comparison' => $this->compareChannelsForCampaign($campaignId)
        ];
    }

    /**
     * Obtenir les détails d'un canal pour une campagne
     */
    protected function getChannelDetails(int $campaignId, string $channel): array
    {
        $logs = CampaignLog::where('campaign_id', $campaignId)
            ->where('channel', $channel)
            ->get();

        $sent = count(array_filter($logs, fn($l) => $l->status === 'sent'));
        $delivered = count(array_filter($logs, fn($l) => $l->status === 'delivered'));
        $opened = count(array_filter($logs, fn($l) => $l->status === 'opened'));
        $clicked = count(array_filter($logs, fn($l) => $l->status === 'clicked'));
        $failed = count(array_filter($logs, fn($l) => $l->status === 'failed'));

        return [
            'total' => $logs->count(),
            'sent' => $sent,
            'delivered' => $delivered,
            'opened' => $opened,
            'clicked' => $clicked,
            'failed' => $failed,
            'cost' => array_sum(array_map(fn($l) => $l->cost ?? 0, $logs)),
            'rates' => [
                'delivery_rate' => $this->calculateRate($delivered, $sent),
                'open_rate' => $this->calculateRate($opened, $delivered),
                'click_rate' => $this->calculateRate($clicked, $delivered),
                'failure_rate' => $this->calculateRate($failed, count($logs))
            ]
        ];
    }

    /**
     * Comparer les canaux d'une campagne
     */
    protected function compareChannelsForCampaign(int $campaignId): array
    {
        $emailLogs = CampaignLog::where('campaign_id', $campaignId)
            ->where('channel', 'email')
            ->get();

        $smsLogs = CampaignLog::where('campaign_id', $campaignId)
            ->where('channel', 'sms')
            ->get();

        return [
            'volume' => [
                'email' => count($emailLogs),
                'sms' => count($smsLogs),
                'winner' => count($emailLogs) > count($smsLogs) ? 'email' : 'sms'
            ],
            'delivery_rate' => [
                'email' => $this->calculateRate(
                    count(array_filter($emailLogs, fn($l) => $l->status === 'delivered')),
                    count($emailLogs)
                ),
                'sms' => $this->calculateRate(
                    count(array_filter($smsLogs, fn($l) => $l->status === 'delivered')),
                    count($smsLogs)
                )
            ],
            'engagement_rate' => [
                'email' => $this->calculateRate(
                    count(array_filter($emailLogs, fn($l) => $l->status === 'opened')),
                    count(array_filter($emailLogs, fn($l) => $l->status === 'delivered'))
                ),
                'sms' => $this->calculateRate(
                    count(array_filter($smsLogs, fn($l) => $l->status === 'opened')),
                    count(array_filter($smsLogs, fn($l) => $l->status === 'delivered'))
                )
            ],
            'cost' => [
                'email' => array_sum(array_map(fn($l) => $l->cost ?? 0, $emailLogs)),
                'sms' => array_sum(array_map(fn($l) => $l->cost ?? 0, $smsLogs)),
                'total' => array_sum(array_map(fn($l) => $l->cost ?? 0, $emailLogs)) + array_sum(array_map(fn($l) => $l->cost ?? 0, $smsLogs))
            ],
            'cost_per_delivery' => [
                'email' => count(array_filter($emailLogs, fn($l) => $l->status === 'delivered')) > 0
                    ? array_sum(array_map(fn($l) => $l->cost ?? 0, $emailLogs)) / count(array_filter($emailLogs, fn($l) => $l->status === 'delivered'))
                    : 0,
                'sms' => count(array_filter($smsLogs, fn($l) => $l->status === 'delivered')) > 0
                    ? array_sum(array_map(fn($l) => $l->cost ?? 0, $smsLogs)) / count(array_filter($smsLogs, fn($l) => $l->status === 'delivered'))
                    : 0
            ]
        ];
    }

    /**
     * Obtenir la timeline d'une campagne
     */
    protected function getCampaignTimeline(int $campaignId): array
    {
        $logs = EmailLog::whereHas('message', function ($query) use ($campaignId) {
            $query->where('campaign_id', $campaignId);
        })->orderBy('occurred_at', 'ASC')->get();

        $timeline = [];
        $events = ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'];

        foreach ($events as $event) {
            $eventLogs = array_filter($logs, fn($l) => $l->event_type === $event);

            if (count($eventLogs) > 0) {
                $timeline[$event] = [
                    'count' => count($eventLogs),
                    'first_at' => reset($eventLogs)->occurred_at ?? null,
                    'last_at' => end($eventLogs)->occurred_at ?? null
                ];
            }
        }

        return $timeline;
    }

    /**
     * Obtenir les liens les plus cliqués
     */
    protected function getTopClickedLinks(int $campaignId, int $limit = 10): array
    {
        $logs = EmailLog::whereHas('message', function ($query) use ($campaignId) {
            $query->where('campaign_id', $campaignId);
        })->where('event_type', 'clicked')
            ->whereNotNull('link_clicked')
            ->get();

        $linkCounts = [];

        foreach ($logs as $log) {
            $link = $log->link_clicked;
            if (!isset($linkCounts[$link])) {
                $linkCounts[$link] = 0;
            }
            $linkCounts[$link]++;
        }

        arsort($linkCounts);

        return array_slice($linkCounts, 0, $limit, true);
    }

    /**
     * Obtenir la distribution géographique
     */
    protected function getGeographicDistribution(int $campaignId): array
    {
        $logs = EmailLog::whereHas('message', function ($query) use ($campaignId) {
            $query->where('campaign_id', $campaignId);
        })->whereNotNull('location')->get();

        $locations = [];

        foreach ($logs as $log) {
            $location = $log->location;
            if (!isset($locations[$location])) {
                $locations[$location] = 0;
            }
            $locations[$location]++;
        }

        arsort($locations);

        return $locations;
    }

    /**
     * Obtenir l'évolution temporelle (par jour/heure)
     */
    public function getTimeSeriesData(int $campaignId, string $interval = 'day'): array
    {
        $logs = CampaignLog::where('campaign_id', $campaignId)
            ->whereNotNull('sent_at')
            ->orderBy('sent_at')
            ->get();

        $series = [];

        foreach ($logs as $log) {
            $timestamp = $log->sent_at;

            if ($interval === 'hour') {
                $key = date('Y-m-d H:00', strtotime($timestamp));
            } else {
                $key = date('Y-m-d', strtotime($timestamp));
            }

            if (!isset($series[$key])) {
                $series[$key] = [
                    'sent' => 0,
                    'delivered' => 0,
                    'opened' => 0,
                    'clicked' => 0,
                    'failed' => 0
                ];
            }

            $series[$key]['sent']++;

            if ($log->status === 'delivered') $series[$key]['delivered']++;
            if ($log->status === 'opened') $series[$key]['opened']++;
            if ($log->status === 'clicked') $series[$key]['clicked']++;
            if ($log->status === 'failed') $series[$key]['failed']++;
        }

        return $series;
    }

    /**
     * Calculer un taux en pourcentage
     */
    protected function calculateRate(int $numerator, int $denominator): float
    {
        if ($denominator == 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    /**
     * Comparer plusieurs campagnes
     */
    public function compareCampaigns(array $campaignIds): array
    {
        $comparisons = [];

        foreach ($campaignIds as $campaignId) {
            $campaign = EmailCampaign::find($campaignId);
            if ($campaign) {
                $comparisons[] = [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'sent' => $campaign->sent_count,
                    'open_rate' => $campaign->getOpenRate(),
                    'click_rate' => $campaign->getClickRate(),
                    'bounce_rate' => $campaign->getBounceRate()
                ];
            }
        }

        return $comparisons;
    }

    /**
     * Obtenir les meilleures campagnes (top performers)
     */
    public function getTopPerformingCampaigns(int $limit = 10, string $metric = 'open_rate'): array
    {
        $campaigns = EmailCampaign::where('status', 'completed')
            ->where('sent_count', '>', 0)
            ->get();

        $ranked = array_map(function ($campaign) use ($metric) {
            return [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'sent' => $campaign->sent_count,
                'open_rate' => $campaign->getOpenRate(),
                'click_rate' => $campaign->getClickRate(),
                'metric_value' => match ($metric) {
                    'open_rate' => $campaign->getOpenRate(),
                    'click_rate' => $campaign->getClickRate(),
                    'delivery_rate' => $campaign->sent_count > 0
                        ? $this->calculateRate($campaign->delivered_count, $campaign->sent_count)
                        : 0,
                    default => 0
                }
            ];
        }, $campaigns);

        // Sort by metric_value descending
        usort($ranked, fn($a, $b) => $b['metric_value'] <=> $a['metric_value']);

        // Take top $limit
        return array_slice($ranked, 0, $limit);
    }
}
