<?php

namespace Modules\Wallet\Services\Gateways;

/**
 * Wave Gateway
 *
 * Intégration avec Wave (Côte d'Ivoire, Sénégal, Burkina Faso, Mali, Bénin, Ouganda)
 * Documentation: https://docs.wave.com/
 */
class WaveGateway extends AbstractPaymentGateway
{
    private const SANDBOX_URL = 'https://api.wave.com/v1/';
    private const PRODUCTION_URL = 'https://api.wave.com/v1/';

    /**
     * {@inheritdoc}
     */
    protected function loadConfig(): void
    {
        $this->config = [
            'api_key' => $this->settingsService->get('wave_api_key', ''),
            'secret_key' => $this->settingsService->get('wave_secret_key', ''),
            'business_id' => $this->settingsService->get('wave_business_id', ''),
            'test_mode' => (bool)$this->settingsService->get('wave_test_mode', true),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Wave';
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'wave';
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePayment(array $data): array
    {
        // Validation
        $validation = $this->validateRequiredFields($data, [
            'amount', 'currency', 'reference', 'customer'
        ]);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => 'Champs requis manquants: ' . implode(', ', $validation['missing_fields'])
            ];
        }

        // Préparer les données pour Wave
        $payload = [
            'amount' => $this->formatAmount($data['amount'], false), // Wave utilise le montant en XOF direct
            'currency' => $data['currency'],
            'error_url' => $data['cancel_url'] ?? $data['return_url'],
            'success_url' => $data['return_url'],
        ];

        $this->logInfo('Initiating Wave payment', ['reference' => $data['reference']]);

        // Appel API Wave - Create Checkout Session
        $response = $this->httpRequest(
            $this->getBaseUrl() . 'checkout/sessions',
            'POST',
            $payload,
            [
                'Authorization: Bearer ' . $this->getConfigValue('api_key'),
                'Content-Type: application/json'
            ]
        );

        if (!$response['success']) {
            $this->logError('Wave API call failed', [
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

        // Vérifier la réponse Wave
        if (isset($body['wave_launch_url'])) {
            return [
                'success' => true,
                'payment_url' => $body['wave_launch_url'],
                'transaction_id' => $body['id'] ?? $data['reference'],
                'error' => null
            ];
        } else {
            $this->logError('Wave payment initialization failed', ['response' => $body]);

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
        $this->logInfo('Verifying Wave payment', ['transaction_id' => $transactionId]);

        // Appel API Wave - Get Checkout Session
        $response = $this->httpRequest(
            $this->getBaseUrl() . 'checkout/sessions/' . $transactionId,
            'GET',
            [],
            [
                'Authorization: Bearer ' . $this->getConfigValue('api_key')
            ]
        );

        if (!$response['success']) {
            $this->logError('Wave verification failed', [
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

        // Interpréter le statut Wave
        $status = strtoupper($body['status'] ?? '');

        if ($status === 'COMPLETE') {
            return [
                'status' => 'SUCCESS',
                'amount' => (float)($body['amount'] ?? 0),
                'currency' => $body['currency'] ?? 'XOF',
                'transaction_id' => $transactionId,
                'reference' => $body['client_reference'] ?? $transactionId,
                'message' => 'Paiement confirmé'
            ];
        } elseif ($status === 'PENDING') {
            return [
                'status' => 'PENDING',
                'amount' => null,
                'currency' => 'XOF',
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => 'Paiement en attente'
            ];
        } else {
            // CANCELLED, EXPIRED, etc.
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
        $this->logInfo('Handling Wave callback', ['payload' => $payload]);

        // Vérifier la signature Wave (HMAC SHA256)
        $signature = $payload['wave_signature'] ?? '';
        unset($payload['wave_signature']);

        $expectedSignature = $this->generateHash(
            json_encode($payload),
            $this->getConfigValue('secret_key')
        );

        if (!$this->verifyHash(json_encode($payload), $signature, $this->getConfigValue('secret_key'))) {
            $this->logError('Wave callback signature verification failed', [
                'expected' => $expectedSignature,
                'received' => $signature
            ]);

            return [
                'valid' => false,
                'status' => 'FAILED',
                'transaction_id' => null,
                'reference' => null,
                'amount' => null,
                'error' => 'Signature invalide'
            ];
        }

        $transactionId = $payload['id'] ?? null;

        if (!$transactionId) {
            $this->logError('Wave callback missing transaction ID', ['payload' => $payload]);

            return [
                'valid' => false,
                'status' => 'FAILED',
                'transaction_id' => null,
                'reference' => null,
                'amount' => null,
                'error' => 'Transaction ID manquant'
            ];
        }

        // Interpréter le statut du callback
        $status = strtoupper($payload['status'] ?? '');

        if ($status === 'COMPLETE') {
            return [
                'valid' => true,
                'status' => 'SUCCESS',
                'transaction_id' => $transactionId,
                'reference' => $payload['client_reference'] ?? $transactionId,
                'amount' => (float)($payload['amount'] ?? 0),
                'error' => null
            ];
        } else {
            return [
                'valid' => true,
                'status' => $status === 'PENDING' ? 'PENDING' : 'FAILED',
                'transaction_id' => $transactionId,
                'reference' => $payload['client_reference'] ?? $transactionId,
                'amount' => null,
                'error' => $status !== 'PENDING' ? 'Paiement échoué ou annulé' : null
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        $apiKey = $this->getConfigValue('api_key');
        $businessId = $this->getConfigValue('business_id');

        return !empty($apiKey) && !empty($businessId);
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
