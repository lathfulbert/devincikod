<?php

namespace Modules\EmailMarketing\Gateways;

class TwilioEmailGateway implements EmailGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Envoyer un email unique via SendGrid API
     */
    public function send(string $to, string $subject, string $html, array $options = []): array
    {
        $apiKey = $this->config['api_key'] ?? '';
        $fromEmail = $this->config['from_email'] ?? '';
        $fromName = $this->config['from_name'] ?? '';

        if (empty($apiKey) || empty($fromEmail)) {
            return [
                'success' => false,
                'message' => 'Missing API Key or From Email configuration'
            ];
        }

        $data = [
            'personalizations' => [
                [
                    'to' => [
                        ['email' => $to]
                    ],
                    'subject' => $subject
                ]
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $html
                ]
            ]
        ];

        return $this->makeRequest($apiKey, $data);
    }

    /**
     * Envoyer des emails en masse
     */
    public function sendBulk(array $recipients, string $subject, string $html, array $options = []): array
    {
        // SendGrid supports up to 1000 recipients per request
        // For simplicity, we'll iterate or use personalizations
        // But to keep it simple and robust, let's loop here or use a batch endpoint if available.
        // SendGrid V3 allows multiple 'to' in personalizations, but that exposes all emails to everyone unless using individual personalizations.

        $successCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            // Replace variables if needed, but for now just send same content
            // Ideally we should process template variables here

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

    /**
     * Obtenir le statut de livraison
     */
    public function getDeliveryStatus(string $messageId): array
    {
        // SendGrid doesn't provide a simple "get status by ID" API for free/standard tiers easily without Event Webhook.
        // We'll return unknown or implement if needed.
        return [
            'status' => 'unknown',
            'delivered_at' => null,
            'opened_at' => null
        ];
    }

    /**
     * Obtenir les crédits restants
     */
    public function getCredits(): ?float
    {
        return null; // Not applicable for SendGrid/Twilio usually
    }

    /**
     * Valider la configuration
     */
    public function validateConfig(): bool
    {
        return !empty($this->config['api_key']) && !empty($this->config['from_email']);
    }

    /**
     * Obtenir le nom du gateway
     */
    public function getName(): string
    {
        return 'Twilio / SendGrid';
    }

    /**
     * Make API Request
     */
    protected function makeRequest(string $apiKey, array $data): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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

        // SendGrid returns 202 Accepted for success
        if ($httpCode >= 200 && $httpCode < 300) {
            // Header X-Message-Id contains the ID, but we might not capture headers here easily without callback.
            // For now, generate a local ID or leave empty.
            return [
                'success' => true,
                'message_id' => uniqid('sg_'),
                'message' => 'Queued',
                'gateway_response' => ['status' => $httpCode]
            ];
        }

        return [
            'success' => false,
            'message' => 'API Error: ' . $httpCode . ' - ' . $response,
            'gateway_response' => json_decode($response, true)
        ];
    }
}
