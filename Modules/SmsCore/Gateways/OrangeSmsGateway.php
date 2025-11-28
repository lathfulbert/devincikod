<?php

namespace Modules\SmsCore\Gateways;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class OrangeSmsGateway implements SmsGatewayInterface
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected ?string $accessToken = null;

    public function __construct(array $config = [])
    {
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->baseUrl = $config['base_url'] ?? 'https://api.orange.com';
    }

    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        try {
            // Ensure we have access token
            if (!$this->accessToken) {
                $this->authenticate();
            }

            $url = $this->baseUrl . '/smsmessaging/v1/outbound/' . urlencode($senderId) . '/requests';

            $payload = [
                'outboundSMSMessageRequest' => [
                    'address' => 'tel:' . $this->formatPhoneNumber($to),
                    'senderAddress' => 'tel:' . $senderId,
                    'outboundSMSTextMessage' => [
                        'message' => $message
                    ]
                ]
            ];

            $response = $this->makeRequest('POST', $url, $payload);

            return [
                'status' => 'success',
                'message_id' => $response['outboundSMSMessageRequest']['resourceURL'] ?? null,
                'to' => $to,
                'gateway' => 'OrangeSMS',
                'raw_response' => $response
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'to' => $to,
                'gateway' => 'OrangeSMS'
            ];
        }
    }

    public function getBalance(): float
    {
        // Orange SMS API doesn't provide direct balance endpoint
        // Would need to implement via customer portal or different method
        return 0.0;
    }

    public function getName(): string
    {
        return 'OrangeSmsGateway';
    }

    protected function authenticate(): void
    {
        $url = $this->baseUrl . '/oauth/v2/token';

        $data = [
            'grant_type' => 'client_credentials'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->clientSecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException("Authentication failed: HTTP $httpCode");
        }

        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'] ?? null;

        if (!$this->accessToken) {
            throw new \RuntimeException("Failed to obtain access token");
        }
    }

    protected function makeRequest(string $method, string $url, array $data = []): array
    {
        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL Error: " . $error);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException("HTTP Error $httpCode: " . $response);
        }

        return json_decode($response, true) ?? [];
    }

    protected function formatPhoneNumber(string $phone): string
    {
        // Remove common formatting
        return preg_replace('/[^0-9+]/', '', $phone);
    }
}
