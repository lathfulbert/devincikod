<?php

namespace Modules\SmsCore\Models;

use App\Core\Database\Model;

class SmsCampaign extends Model
{
    protected static string $table = 'sms_campaigns';

    protected array $fillable = [
        'name',
        'message',
        'sender_id',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by'
    ];

    public function getProgress(): float
    {
        if ($this->total_recipients == 0) {
            return 0;
        }

        return round((($this->sent_count + $this->failed_count) / $this->total_recipients) * 100, 2);
    }

    public function isCompleted(): bool
    {
        return ($this->sent_count + $this->failed_count) >= $this->total_recipients;
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'sending',
            'started_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed'
        ]);
    }
}
