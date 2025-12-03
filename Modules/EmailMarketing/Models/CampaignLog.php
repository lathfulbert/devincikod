<?php

namespace Modules\EmailMarketing\Models;

use App\Core\Database\Model;

/**
 * Logs centralisés pour tous les canaux (Email + SMS)
 */
class CampaignLog extends Model
{
    protected static string $table = 'campaign_logs';

    protected array $fillable = [
        'campaign_id',
        'campaign_type',
        'channel',
        'contact_id',
        'recipient_identifier',
        'message_id',
        'gateway',
        'status',
        'cost',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'error',
        'metadata'
    ];

    /**
     * Logger un envoi email
     */
    public static function logEmail(
        int $campaignId,
        string $campaignType,
        int $contactId,
        string $email,
        array $data
    ): self {
        return static::create([
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignType,
            'channel' => 'email',
            'contact_id' => $contactId,
            'recipient_identifier' => $email,
            'message_id' => $data['message_id'] ?? null,
            'gateway' => $data['gateway'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'cost' => $data['cost'] ?? 0,
            'sent_at' => $data['sent_at'] ?? null,
            'metadata' => json_encode($data['metadata'] ?? [])
        ]);
    }

    /**
     * Logger un envoi SMS
     */
    public static function logSms(
        int $campaignId,
        string $campaignType,
        int $contactId,
        string $phone,
        array $data
    ): self {
        return static::create([
            'campaign_id' => $campaignId,
            'campaign_type' => $campaignType,
            'channel' => 'sms',
            'contact_id' => $contactId,
            'recipient_identifier' => $phone,
            'message_id' => $data['message_id'] ?? null,
            'gateway' => $data['gateway'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'cost' => $data['cost'] ?? 0,
            'sent_at' => $data['sent_at'] ?? null,
            'metadata' => json_encode($data['metadata'] ?? [])
        ]);
    }

    /**
     * Mettre à jour le statut
     */
    public function updateStatus(string $status, array $data = []): void
    {
        $updateData = ['status' => $status];

        if ($status === 'delivered' && !$this->delivered_at) {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        }

        if ($status === 'opened' && !$this->opened_at) {
            $updateData['opened_at'] = date('Y-m-d H:i:s');
        }

        if ($status === 'clicked' && !$this->clicked_at) {
            $updateData['clicked_at'] = date('Y-m-d H:i:s');
        }

        if (isset($data['error'])) {
            $updateData['error'] = $data['error'];
        }

        $this->update($updateData);
    }

    /**
     * Statistiques par campagne
     */
    public static function getStatsByCampaign(int $campaignId): array
    {
        $logs = static::where('campaign_id', $campaignId)->get();

        return [
            'total' => $logs->count(),
            'by_channel' => [
                'email' => $logs->where('channel', 'email')->count(),
                'sms' => $logs->where('channel', 'sms')->count()
            ],
            'by_status' => [
                'sent' => $logs->where('status', 'sent')->count(),
                'delivered' => $logs->where('status', 'delivered')->count(),
                'opened' => $logs->where('status', 'opened')->count(),
                'clicked' => $logs->where('status', 'clicked')->count(),
                'failed' => $logs->where('status', 'failed')->count()
            ],
            'total_cost' => $logs->sum('cost')
        ];
    }

    /**
     * Statistiques par canal
     */
    public static function getStatsByChannel(string $channel): array
    {
        $logs = static::where('channel', $channel)->get();

        return [
            'total' => $logs->count(),
            'delivered' => $logs->where('status', 'delivered')->count(),
            'opened' => $logs->where('status', 'opened')->count(),
            'clicked' => $logs->where('status', 'clicked')->count(),
            'failed' => $logs->where('status', 'failed')->count(),
            'total_cost' => $logs->sum('cost')
        ];
    }
}
