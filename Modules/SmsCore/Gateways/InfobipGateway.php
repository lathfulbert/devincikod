<?php

namespace Modules\SmsCore\Gateways;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;
use Modules\Settings\Models\SmsGateway;

class InfobipGateway implements SmsGatewayInterface
{
    private SmsGateway $config;

    public function __construct(SmsGateway $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        try {
            $payload = [
                'messages' => [
                    [
                        'from' => $senderId,
                        'destinations' => [
                            ['to' => $to]
                        ],
                        'text' => $message
                    ]
                ]
            ];

            $ch = curl_init($this->config->api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: App ' . $this->config->api_key,
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($httpCode === 200) {
                $messageResult = $responseData['messages'][0] ?? [];
                $status = $messageResult['status'] ?? [];

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'gateway_message_id' => $messageResult['messageId'] ?? null,
                    'gateway_response' => $responseData,
                    'status_code' => $status['groupId'] ?? null,
                    'status_description' => $status['description'] ?? null,
                    'sent_at' => date('Y-m-d H:i:s')
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $responseData['requestError']['serviceException']['text'] ?? 'Unknown error',
                'gateway_response' => $responseData,
                'http_code' => $httpCode,
                'sent_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'gateway_response' => null,
                'sent_at' => date('Y-m-d H:i:s')
            ];
        }
    }

    public function getBalance(): float
    {
        try {
            $ch = curl_init('https://api.infobip.com/account/1/balance');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: App ' . $this->config->api_key,
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return (float)($data['balance'] ?? 0);
            }

            return 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function getName(): string
    {
        return $this->config->name;
    }
}
