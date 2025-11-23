<?php

namespace Modules\Settings\Models;

use App\Core\Database\Model;

class WebhookLog extends Model
{
    protected static string $table = 'webhook_logs';
    protected array $fillable = ['webhook_id', 'payload', 'response', 'http_code', 'error', 'duration'];

    /**
     * Get webhook
     */
    public function webhook()
    {
        return Webhook::find($this->webhook_id);
    }

    /**
     * Check if call was successful
     */
    public function isSuccessful(): bool
    {
        return $this->http_code >= 200 && $this->http_code < 300;
    }

    /**
     * Clean old logs (keep last 30 days)
     */
    public static function cleanup(int $days = 30): int
    {
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        return static::where('created_at', '<', $date)->delete();
    }
}
