<?php

namespace Modules\EmailMarketing\Gateways;

/**
 * Gateway Elasticmail pour l'envoi d'e-mails via l'API HTTP Elasticmail
 */
class ElasticMailGateway implements EmailGatewayInterface
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

        if (empty($apiKey) || empty($fromEmail)) {
            return [
                'success' => false,
                'message' => 'Missing API Key or From Email configuration'
            ];
        }

        $postData = [
            'apikey' => $apiKey,
            'from' => $fromEmail,
            'fromName' => $fromName,
            'to' => $to,
            'subject' => $subject,
            'bodyHtml' => $html,
            'isTransactional' => true
        ];

        $ch = curl_init('https://api.elasticemail.com/v2/email/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
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

        $result = json_decode($response, true);
        if ($httpCode === 200 && isset($result['success']) && $result['success']) {
            return [
                'success' => true,
                'message_id' => $result['data']['messageid'] ?? uniqid('em_'),
                'message' => 'Queued',
                'gateway_response' => $result
            ];
        }
        return [
            'success' => false,
            'message' => 'API Error: ' . ($result['error'] ?? $response),
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
        return 'ElasticMail';
    }
}
