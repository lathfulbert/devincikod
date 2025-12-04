<?php

namespace Modules\SmsCore\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class SmsQueue extends Model
{
    use HasAuthor;

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
        'sent_at',
        'created_by',
        'updated_by'
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
