<?php

namespace Modules\EmailMarketing\Models;

use App\Core\Database\Traits\HasAuthor;

use App\Core\Database\Model;

class EmailCampaign extends Model
{
    use HasAuthor;

    protected static string $table = 'email_campaigns';

    protected array $fillable = [

        'name',
        'subject',
        'template_id',
        'from_name',
        'from_email',
        'reply_to',
        'status',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'unsubscribed_count',
        'failed_count',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by',
        'contact_ids',
        'segments',
        'use_personalization',
        'updated_by'
    ];

    /**
     * Calculer le taux de progression
     */
    public function getProgress(): float
    {
        if ($this->total_recipients == 0) {
            return 0;
        }

        $processed = $this->sent_count + $this->failed_count;
        return round(($processed / $this->total_recipients) * 100, 2);
    }

    /**
     * Calculer le taux d'ouverture
     */
    public function getOpenRate(): float
    {
        if ($this->delivered_count == 0) {
            return 0;
        }

        return round(($this->opened_count / $this->delivered_count) * 100, 2);
    }

    /**
     * Calculer le taux de clic
     */
    public function getClickRate(): float
    {
        if ($this->delivered_count == 0) {
            return 0;
        }

        return round(($this->clicked_count / $this->delivered_count) * 100, 2);
    }

    /**
     * Calculer le taux de rebond
     */
    public function getBounceRate(): float
    {
        if ($this->sent_count == 0) {
            return 0;
        }

        return round(($this->bounced_count / $this->sent_count) * 100, 2);
    }

    /**
     * Vérifier si la campagne est terminée
     */
    public function isCompleted(): bool
    {
        return ($this->sent_count + $this->failed_count) >= $this->total_recipients;
    }

    /**
     * Marquer comme démarrée
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'sending',
            'started_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marquer comme terminée
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marquer comme échouée
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed'
        ]);
    }

    /**
     * Incrémenter le compteur d'envois
     */
    public function incrementSent(): void
    {
        $this->increment('sent_count');
    }

    /**
     * Incrémenter le compteur de livraisons
     */
    public function incrementDelivered(): void
    {
        $this->increment('delivered_count');
    }

    /**
     * Incrémenter le compteur d'ouvertures
     */
    public function incrementOpened(): void
    {
        $this->increment('opened_count');
    }

    /**
     * Incrémenter le compteur de clics
     */
    public function incrementClicked(): void
    {
        $this->increment('clicked_count');
    }

    /**
     * Incrémenter le compteur d'échecs
     */
    public function incrementFailed(): void
    {
        $this->increment('failed_count');
    }

    /**
     * Relation avec le template
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Relation avec les messages
     */
    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'campaign_id');
    }
}
