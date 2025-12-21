<?php

namespace Modules\EmailMarketing\Gateways;

/**
 * Gateway Infobip pour l'envoi d'e-mails via l'API HTTP Infobip
 */
class InfobipEmailGateway implements EmailGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $html, array $options = []): array
    {
        $apiKey = $this->config['api_key'] ?? '';
        $fromEmail = $this->config['from_email'] ?? '';
        $fromName = $this->config['from_name'] ?? '';
        $baseUrl = $this->config['base_url'] ?? 'https://api.infobip.com';

        if (empty($apiKey) || empty($fromEmail)) {
            return [
                'success' => false,
                'message' => 'Missing API Key or From Email configuration'
            ];
        }

        $postData = [
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName
            ],
            'to' => [
                [ 'email' => $to ]
            ],
            'subject' => $subject,
            'html' => $html
        ];

        $ch = curl_init($baseUrl . '/email/3/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: App ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'Curl error: ' . $error
            ];
        }

        $result = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($result['messages'][0]['messageId'])) {
            return [
                'success' => true,
                'message_id' => $result['messages'][0]['messageId'],
                'message' => 'Queued',
                'gateway_response' => $result
            ];
        }
        return [
            'success' => false,
            'message' => 'API Error: ' . ($result['requestError']['serviceException']['text'] ?? $response),
            'gateway_response' => $result
        ];
    }

    public function sendBulk(array $recipients, string $subject, string $html, array $options = []): array
    {
        $successCount = 0;
        $failedCount = 0;
        $results = [];
        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            $result = $this->send($email, $subject, $html, $options);
            $results[$email] = $result;
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }
        return [
            'success' => $failedCount === 0,
            'sent' => $successCount,
            'failed' => $failedCount,
            'results' => $results
        ];
    }

    public function getDeliveryStatus(string $messageId): array
    {
        return [
            'status' => 'unknown',
            'delivered_at' => null,
            'opened_at' => null
        ];
    }

    public function getCredits(): ?float
    {
        return null;
    }

    public function validateConfig(): bool
    {
        return !empty($this->config['api_key']) && !empty($this->config['from_email']);
    }

    public function getName(): string
    {
        return 'Infobip';
    }
}
