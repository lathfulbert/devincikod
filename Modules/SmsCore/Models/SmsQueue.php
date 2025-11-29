<?php

namespace Modules\SmsCore\Models;

use App\Core\Database\Model;

class SmsQueue extends Model
{
    protected static string $table = 'sms_queue';

    protected array $fillable = [
        'campaign_id',
        'recipient',
        'message',
        'sender_id',
        'status',
        'attempts',
        'error_message',
        'scheduled_at',
        'sent_at'
    ];

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'attempts' => $this->attempts + 1
        ]);
    }
}
