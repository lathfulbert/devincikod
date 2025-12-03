<?php

namespace Modules\EmailMarketing\Models;

use App\Core\Database\Model;

class EmailLog extends Model
{
    protected static string $table = 'email_logs';

    protected array $fillable = [
        'message_id',
        'event_type',
        'ip_address',
        'user_agent',
        'location',
        'link_clicked',
        'bounce_type',
        'bounce_reason',
        'event_data',
        'occurred_at'
    ];

    public $timestamps = false;

    /**
     * Relation avec le message
     */
    public function message()
    {
        return $this->belongsTo(EmailMessage::class, 'message_id');
    }

    /**
     * Logger un événement
     */
    public static function logEvent(
        int $messageId,
        string $eventType,
        array $data = []
    ): self {
        return static::create([
            'message_id' => $messageId,
            'event_type' => $eventType,
            'ip_address' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'location' => $data['location'] ?? null,
            'link_clicked' => $data['link'] ?? null,
            'bounce_type' => $data['bounce_type'] ?? null,
            'bounce_reason' => $data['bounce_reason'] ?? null,
            'event_data' => json_encode($data),
            'occurred_at' => date('Y-m-d H:i:s')
        ]);
    }
}
