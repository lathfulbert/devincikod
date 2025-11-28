<?php

namespace Modules\SmsCore\Gateways;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class InfobipGateway implements SmsGatewayInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $senderId;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = $config['base_url'] ?? 'https://api.infobip.com';
        $this->senderId = $config['sender_id'] ?? 'InfoSMS';
    }

    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        $url = $this->baseUrl . '/sms/2/text/advanced';

        $payload = [
            'messages' => [
                [
                    'from' => $senderId ?: $this->senderId,
                    'destinations' => [
                        ['to' => $this->formatPhoneNumber($to)]
                    ],
                    'text' => $message
                ]
            ]
        ];

        // Add optional parameters
        if (isset($options['notify_url'])) {
            $payload['messages'][0]['notifyUrl'] = $options['notify_url'];
        }

        if (isset($options['validity_period'])) {
            $payload['messages'][0]['validityPeriod'] = $options['validity_period'];
        }

        try {
            $response = $this->makeRequest('POST', $url, $payload);

            return [
                'status' => 'success',
                'message_id' => $response['messages'][0]['messageId'] ?? null,
                'to' => $to,
                'gateway' => 'Infobip',
                'raw_response' => $response
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'to' => $to,
                'gateway' => 'Infobip'
            ];
        }
    }

    public function getBalance(): float
    {
        try {
            $url = $this->baseUrl . '/account/1/balance';
            $response = $this->makeRequest('GET', $url);

            return (float) ($response['balance'] ?? 0.0);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getName(): string
    {
        return 'InfobipGateway';
    }

    protected function makeRequest(string $method, string $url, array $data = []): array
    {
        $ch = curl_init();

        $headers = [
            'Authorization: App ' . $this->apiKey,
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
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure + prefix
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
