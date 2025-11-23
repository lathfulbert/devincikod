<?php

namespace Modules\Settings\Models;

use App\Core\Database\Model;

class Webhook extends Model
{
    protected static string $table = 'webhooks';
    protected array $fillable = ['name', 'url', 'events', 'secret', 'is_active', 'headers', 'retry_count', 'timeout'];
    protected array $casts = [
        'is_active' => 'boolean',
        'events' => 'json',
        'headers' => 'json',
        'retry_count' => 'integer',
        'timeout' => 'integer'
    ];

    /**
     * Get active webhooks for an event
     */
    public static function getByEvent(string $event): array
    {
        $webhooks = static::where('is_active', true)->get();
        $result = [];

        foreach ($webhooks as $webhook) {
            $events = is_string($webhook->events) ? json_decode($webhook->events, true) : $webhook->events;
            if (in_array($event, $events)) {
                $result[] = $webhook;
            }
        }

        return $result;
    }

    /**
     * Send webhook request
     */
    public function send(array $payload): array
    {
        $ch = curl_init($this->url);

        $headers = $this->headers ?? [];
        $headers['Content-Type'] = 'application/json';

        if ($this->secret) {
            $headers['X-Webhook-Secret'] = $this->secret;
            $headers['X-Webhook-Signature'] = hash_hmac('sha256', json_encode($payload), $this->secret);
        }

        $headersList = [];
        foreach ($headers as $key => $value) {
            $headersList[] = "$key: $value";
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headersList,
            CURLOPT_TIMEOUT => $this->timeout ?? 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = microtime(true) - $startTime;

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // Log the webhook call
        $this->logCall($payload, $response, $httpCode, $error, $duration);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'duration' => $duration
        ];
    }

    /**
     * Log webhook call
     */
    protected function logCall(array $payload, $response, int $httpCode, string $error, float $duration): void
    {
        WebhookLog::create([
            'webhook_id' => $this->id,
            'payload' => json_encode($payload),
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $error,
            'duration' => $duration,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get webhook logs
     */
    public function logs()
    {
        return WebhookLog::where('webhook_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get();
    }

    /**
     * Test webhook
     */
    public function test(): array
    {
        return $this->send([
            'event' => 'webhook.test',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => ['message' => 'Test webhook from SunuFramework']
        ]);
    }
}
