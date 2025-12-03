<?php

namespace Modules\SmsCore\Gateways;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;
use Modules\Settings\Models\SmsGateway;

class OrangeCIGateway implements SmsGatewayInterface
{
    private SmsGateway $config;

    public function __construct(SmsGateway $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        try {
            // 1. Get OAuth token first
            $token = $this->getAccessToken();

            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to obtain access token',
                    'gateway_response' => null,
                    'sent_at' => date('Y-m-d H:i:s')
                ];
            }

            // 2. Determine Sender Address (Technical number) and Sender Name (Display name)
            // Try to get valid numeric address from config first, then from input
            $configSender = $this->formatPhoneNumber($this->config->sender_id ?? '');
            $inputSender = $this->formatPhoneNumber($senderId);

            // Use configured sender ID as technical address if available, otherwise input
            $senderAddress = $configSender ?: $inputSender;

            if (!$senderAddress) {
                return [
                    'success' => false,
                    'message' => 'Invalid Sender Address. Please configure a numeric Sender ID in gateway settings.',
                    'gateway_response' => null,
                    'sent_at' => date('Y-m-d H:i:s')
                ];
            }

            // 3. Format recipient number
            $recipientPhone = $this->formatPhoneNumber($to);

            if (!$recipientPhone) {
                return [
                    'success' => false,
                    'message' => 'Invalid Recipient Number',
                    'gateway_response' => null,
                    'sent_at' => date('Y-m-d H:i:s')
                ];
            }

            // 4. Build API URL with properly URL-encoded sender (tel%3A%2B...)
            $encodedSender = str_replace('tel:', 'tel%3A', str_replace('+', '%2B', $senderAddress));
            $configuration = json_decode($this->config->configuration, true);

            // Build query parameters
            $queryParams = [];
            if (isset($configuration['on_net_only']) && $configuration['on_net_only']) {
                $queryParams['resource_type_parameter_management'] = 'SMS_OCB2';
            }
            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

            $apiUrl = $this->config->api_url . '/' . $encodedSender . '/requests' . $queryString;

            $payload = [
                'outboundSMSMessageRequest' => [
                    'address' => $recipientPhone,
                    'senderAddress' => $senderAddress,
                    'outboundSMSTextMessage' => [
                        'message' => $message
                    ]
                ]
            ];

            // Add senderName if provided (e.g. "TICAFRIQUE")
            // Use input senderId as name if it's different from the technical address
            // OR if it's alphanumeric (which implies it's a name)
            if ($senderId && $senderId !== $senderAddress && $senderId !== $this->config->sender_id) {
                $payload['outboundSMSMessageRequest']['senderName'] = $senderId;
            }

            // Build headers
            $headers = [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json'
            ];

            // Add X-API-Key header if configured
            $xApiKey = $configuration['x_api_key'] ?? null;
            if ($xApiKey) {
                $headers[] = 'X-API-Key: ' . $xApiKey;
            }

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($httpCode === 201 || $httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'gateway_message_id' => $responseData['outboundSMSMessageRequest']['resourceURL'] ?? null,
                    'gateway_response' => $responseData,
                    'sent_at' => date('Y-m-d H:i:s')
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $responseData['error'] ?? 'Unknown error',
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

    private function getAccessToken(): ?string
    {
        // Check if we have a cached token that's still valid
        $cached = $this->getCachedToken();
        if ($cached) {
            return $cached;
        }

        // Request new token
        $configuration = json_decode($this->config->configuration, true);
        $tokenUrl = $configuration['token_url'] ?? 'https://api.orange.com/oauth/v3/token';

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'client_credentials'
        ]));

        $headers = [
            'Authorization: Basic ' . base64_encode($this->config->api_key . ':' . $this->config->api_secret),
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ];

        // Add X-API-Key header if configured
        $xApiKey = $configuration['x_api_key'] ?? null;
        if ($xApiKey) {
            $headers[] = 'X-API-Key: ' . $xApiKey;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $accessToken = $data['access_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;

            if ($accessToken) {
                // Cache the token
                $this->cacheToken($accessToken, $expiresIn);
                return $accessToken;
            }
        }

        // Log error for debugging
        error_log("Orange CI Token Error: HTTP $httpCode - Response: $response - cURL Error: $curlError");

        return null;
    }

    /**
     * Get cached token if still valid
     */
    private function getCachedToken(): ?string
    {
        $cacheFile = $this->getTokenCacheFile();

        if (!file_exists($cacheFile)) {
            return null;
        }

        $data = json_decode(file_get_contents($cacheFile), true);

        if (!is_array($data) || !isset($data['access_token'], $data['expires_at'])) {
            return null;
        }

        // Check if token expires in less than 30 seconds (refresh early)
        if ($data['expires_at'] <= time() + 30) {
            return null;
        }

        return $data['access_token'];
    }

    /**
     * Cache the OAuth token
     */
    private function cacheToken(string $accessToken, int $expiresIn): void
    {
        $cacheFile = $this->getTokenCacheFile();
        $cacheDir = dirname($cacheFile);

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $data = [
            'access_token' => $accessToken,
            'expires_at' => time() + max(60, $expiresIn)
        ];

        file_put_contents($cacheFile, json_encode($data));
    }

    /**
     * Get path to token cache file
     */
    private function getTokenCacheFile(): string
    {
        $cacheDir = dirname(__DIR__, 3) . '/storage/cache/oauth';
        return $cacheDir . '/orange_ci_token_' . $this->config->id . '.json';
    }

    public function getBalance(): float
    {
        // Orange API doesn't provide balance endpoint
        return 0.0;
    }

    public function getName(): string
    {
        return $this->config->name;
    }

    /**
     * Format phone number to Orange API format: tel:+XXXXXXXXXXXX
     *
     * @param string $number Phone number to format
     * @return ?string Formatted phone number or null if invalid/alphanumeric
     */
    private function formatPhoneNumber(string $number): ?string
    {
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $number);

        if (empty($cleaned)) {
            return null;
        }

        // If already has tel: prefix, return as is
        if (strpos($number, 'tel:') === 0) {
            return $number;
        }

        // If doesn't start with +, add +225 (Côte d'Ivoire code)
        if (substr($cleaned, 0, 1) !== '+') {
            $configuration = json_decode($this->config->configuration, true);
            $countryCode = $configuration['country_code'] ?? '+225';

            // For Ivory Coast (+225), we should NOT remove the leading 0 for 10-digit numbers
            if ($countryCode === '+225') {
                $cleaned = $countryCode . $cleaned;
            } else {
                $cleaned = $countryCode . ltrim($cleaned, '0');
            }
        }

        // Return in tel:+XXXXXXXXXXXX format
        return 'tel:' . $cleaned;
    }
}
