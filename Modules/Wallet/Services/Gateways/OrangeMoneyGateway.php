<?php

namespace Modules\Wallet\Services\Gateways;

/**
 * Orange Money Gateway
 *
 * Intégration avec Orange Money CI (Côte d'Ivoire)
 * Documentation: https://developer.orange.com/apis/orange-money-webpay/
 */
class OrangeMoneyGateway extends AbstractPaymentGateway
{
    private const SANDBOX_URL = 'https://api.orange.com/orange-money-webpay/dev/v1/';
    private const PRODUCTION_URL = 'https://api.orange.com/orange-money-webpay/ci/v1/';

    private const AUTH_URL_SANDBOX = 'https://api.orange.com/oauth/v3/token';
    private const AUTH_URL_PRODUCTION = 'https://api.orange.com/oauth/v3/token';

    private ?string $accessToken = null;

    /**
     * {@inheritdoc}
     */
    protected function loadConfig(): void
    {
        $this->config = [
            'client_id' => $this->settingsService->get('orange_money_client_id', ''),
            'client_secret' => $this->settingsService->get('orange_money_client_secret', ''),
            'merchant_key' => $this->settingsService->get('orange_money_merchant_key', ''),
            'test_mode' => (bool)$this->settingsService->get('orange_money_test_mode', true),
            'country' => $this->settingsService->get('orange_money_country', 'CI'), // CI, SN, ML, etc.
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Orange Money';
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'orange_money';
    }

    /**
     * Obtenir un access token OAuth2
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $authUrl = $this->testMode ? self::AUTH_URL_SANDBOX : self::AUTH_URL_PRODUCTION;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $authUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'client_credentials'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode(
                $this->getConfigValue('client_id') . ':' . $this->getConfigValue('client_secret')
            ),
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->accessToken = $data['access_token'] ?? null;
            return $this->accessToken;
        }

        $this->logError('Failed to get Orange Money access token', [
            'http_code' => $httpCode,
            'response' => $response
        ]);

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePayment(array $data): array
    {
        // Validation
        $validation = $this->validateRequiredFields($data, [
            'amount', 'currency', 'reference', 'customer', 'return_url'
        ]);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => 'Champs requis manquants: ' . implode(', ', $validation['missing_fields'])
            ];
        }

        // Obtenir access token
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => 'Impossible d\'obtenir le token d\'authentification'
            ];
        }

        // Préparer les données pour Orange Money
        $payload = [
            'merchant_key' => $this->getConfigValue('merchant_key'),
            'currency' => $data['currency'], // OUV (Orange Unit Value) ou XOF
            'order_id' => $data['reference'],
            'amount' => (int)$data['amount'],
            'return_url' => $data['return_url'],
            'cancel_url' => $data['cancel_url'] ?? $data['return_url'],
            'notif_url' => $data['webhook_url'] ?? '',
            'lang' => 'fr',
            'reference' => $data['description'] ?? 'Rechargement wallet'
        ];

        $this->logInfo('Initiating Orange Money payment', ['order_id' => $data['reference']]);

        // Appel API Orange Money
        $response = $this->httpRequest(
            $this->getBaseUrl() . 'webpayment',
            'POST',
            $payload,
            [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]
        );

        if (!$response['success']) {
            $this->logError('Orange Money API call failed', [
                'error' => $response['error'],
                'status_code' => $response['status_code']
            ]);

            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => $response['error'] ?? 'Erreur lors de l\'initialisation du paiement'
            ];
        }

        $body = $response['body'];

        // Vérifier la réponse Orange Money
        if (isset($body['payment_url'])) {
            return [
                'success' => true,
                'payment_url' => $body['payment_url'],
                'transaction_id' => $body['pay_token'] ?? $data['reference'],
                'error' => null
            ];
        } else {
            $this->logError('Orange Money payment initialization failed', ['response' => $body]);

            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => $body['message'] ?? 'Échec de l\'initialisation du paiement'
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function verifyPayment(string $transactionId): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'status' => 'FAILED',
                'amount' => null,
                'currency' => null,
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => 'Impossible de vérifier le paiement'
            ];
        }

        $this->logInfo('Verifying Orange Money payment', ['transaction_id' => $transactionId]);

        $response = $this->httpRequest(
            $this->getBaseUrl() . 'webpayment/' . $transactionId . '/transactions',
            'GET',
            [],
            [
                'Authorization: Bearer ' . $accessToken
            ]
        );

        if (!$response['success']) {
            $this->logError('Orange Money verification failed', [
                'transaction_id' => $transactionId,
                'error' => $response['error']
            ]);

            return [
                'status' => 'FAILED',
                'amount' => null,
                'currency' => null,
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => $response['error'] ?? 'Erreur de vérification'
            ];
        }

        $body = $response['body'];

        // Interpréter le statut Orange Money
        $status = strtoupper($body['status'] ?? '');

        if ($status === 'SUCCESS' || $status === 'SUCCESSFUL') {
            return [
                'status' => 'SUCCESS',
                'amount' => (float)($body['amount'] ?? 0),
                'currency' => $body['currency'] ?? 'XOF',
                'transaction_id' => $transactionId,
                'reference' => $body['order_id'] ?? $transactionId,
                'message' => 'Paiement confirmé'
            ];
        } elseif ($status === 'PENDING' || $status === 'INITIATED') {
            return [
                'status' => 'PENDING',
                'amount' => null,
                'currency' => 'XOF',
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => 'Paiement en attente'
            ];
        } else {
            return [
                'status' => 'FAILED',
                'amount' => null,
                'currency' => 'XOF',
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => $body['message'] ?? 'Paiement échoué'
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleCallback(array $payload): array
    {
        $this->logInfo('Handling Orange Money callback', ['payload' => $payload]);

        $transactionId = $payload['pay_token'] ?? $payload['transaction_id'] ?? null;

        if (!$transactionId) {
            $this->logError('Orange Money callback missing transaction ID', ['payload' => $payload]);

            return [
                'valid' => false,
                'status' => 'FAILED',
                'transaction_id' => null,
                'reference' => null,
                'amount' => null,
                'error' => 'Transaction ID manquant'
            ];
        }

        // Vérifier le paiement via l'API Orange Money
        $verification = $this->verifyPayment($transactionId);

        return [
            'valid' => $verification['status'] === 'SUCCESS',
            'status' => $verification['status'],
            'transaction_id' => $transactionId,
            'reference' => $verification['reference'],
            'amount' => $verification['amount'],
            'error' => $verification['status'] !== 'SUCCESS' ? $verification['message'] : null
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        $clientId = $this->getConfigValue('client_id');
        $clientSecret = $this->getConfigValue('client_secret');
        $merchantKey = $this->getConfigValue('merchant_key');

        return !empty($clientId) && !empty($clientSecret) && !empty($merchantKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedPaymentMethods(): array
    {
        return ['MOBILE_MONEY'];
    }

    /**
     * Obtenir l'URL de base selon le mode
     */
    private function getBaseUrl(): string
    {
        return $this->testMode ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }
}
