<?php

namespace App\Core\Notifications\Providers\Email;

use App\Core\Contracts\NotificationProviderInterface;
use App\Core\Notifications\ProviderResponse;

/**
 * SendGrid Email Provider
 * 
 * Sends emails using SendGrid API
 */
class SendGridProvider implements NotificationProviderInterface
{
    protected array $config;
    protected string $apiKey;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
    }

    /**
     * Send email via SendGrid API
     */
    public function send(array $payload): object
    {
        try {
            $data = [
                'personalizations' => [
                    [
                        'to' => [['email' => $payload['to']]],
                        'subject' => $payload['subject'],
                    ],
                ],
                'from' => [
                    'email' => $payload['from_email'] ?? $this->config['from_email'],
                    'name' => $payload['from_name'] ?? $this->config['from_name'] ?? 'SunuFramework',
                ],
                'content' => [],
            ];

            // Add HTML content
            if (!empty($payload['body_html'])) {
                $data['content'][] = [
                    'type' => 'text/html',
                    'value' => $payload['body_html'],
                ];
            }

            // Add plain text content
            if (!empty($payload['body_text'])) {
                $data['content'][] = [
                    'type' => 'text/plain',
                    'value' => $payload['body_text'],
                ];
            }

            // Make API request using HTTP Client
            $response = Http()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.sendgrid.com/v3/mail/send', $data);

            if ($response->successful()) {
                $messageId = $response->header('x-message-id') ?? uniqid('sendgrid_');

                return ProviderResponse::success(
                    messageId: $messageId,
                    metadata: ['provider' => 'sendgrid']
                );
            }

            return ProviderResponse::failed(
                error: 'SendGrid API error: ' . $response->body()
            );
        } catch (\Exception $e) {
            return ProviderResponse::failed($e->getMessage());
        }
    }

    /**
     * Health check - verify API key
     */
    public function healthCheck(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(5)->get('https://api.sendgrid.com/v3/scopes');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get provider type
     */
    public function getType(): string
    {
        return 'email';
    }

    /**
     * Get provider name
     */
    public function getName(): string
    {
        return 'sendgrid';
    }
}
