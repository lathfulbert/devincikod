<?php

namespace Modules\EmailMarketing\Models;

use App\Core\Database\Model;

class EmailMessage extends Model
{
    protected static string $table = 'email_messages';

    protected array $fillable = [
        'campaign_id',
        'user_id',
        'to_email',
        'to_name',
        'from_email',
        'from_name',
        'reply_to',
        'subject',
        'body_html',
        'body_text',
        'gateway',
        'status',
        'message_id',
        'gateway_message_id',
        'cost',
        'metadata',
        'gateway_response',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'open_count',
        'click_count',
        'error'
    ];

    /**
     * Relation avec la campagne
     */
    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Relation avec les logs
     */
    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'message_id');
    }

    /**
     * Marquer comme envoyé
     */
    public function markAsSent(string $gatewayMessageId = null): void
    {
        $data = [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ];

        if ($gatewayMessageId) {
            $data['gateway_message_id'] = $gatewayMessageId;
        }

        $this->update($data);
    }

    /**
     * Marquer comme livré
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marquer comme ouvert
     */
    public function markAsOpened(): void
    {
        $this->increment('open_count');

        if (!$this->opened_at) {
            $this->update([
                'status' => 'opened',
                'opened_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Marquer comme cliqué
     */
    public function markAsClicked(): void
    {
        $this->increment('click_count');

        if (!$this->clicked_at) {
            $this->update([
                'status' => 'clicked',
                'clicked_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Marquer comme échoué
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error
        ]);
    }

    /**
     * Marquer comme rebondi
     */
    public function markAsBounced(string $reason = null): void
    {
        $this->update([
            'status' => 'bounced',
            'error' => $reason
        ]);
    }

    /**
     * Vérifier si le message a été envoyé
     */
    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'opened', 'clicked']);
    }

    /**
     * Vérifier si le message a été ouvert
     */
    public function isOpened(): bool
    {
        return in_array($this->status, ['opened', 'clicked']) || $this->open_count > 0;
    }

    /**
     * Vérifier si le message a été cliqué
     */
    public function isClicked(): bool
    {
        return $this->status === 'clicked' || $this->click_count > 0;
    }
}
