<?php

namespace Modules\Wallet\Services\Gateways;

/**
 * PayDunya Gateway
 *
 * Intégration avec PayDunya (Côte d'Ivoire, Sénégal, Bénin, Burkina Faso, etc.)
 * Documentation: https://paydunya.com/developers
 */
class PayDunyaGateway extends AbstractPaymentGateway
{
    private const SANDBOX_URL = 'https://app.paydunya.com/sandbox-api/v1/';
    private const PRODUCTION_URL = 'https://app.paydunya.com/api/v1/';

    /**
     * {@inheritdoc}
     */
    protected function loadConfig(): void
    {
        $this->config = [
            'master_key' => $this->settingsService->get('paydunya_master_key', ''),
            'public_key' => $this->settingsService->get('paydunya_public_key', ''),
            'private_key' => $this->settingsService->get('paydunya_private_key', ''),
            'token' => $this->settingsService->get('paydunya_token', ''),
            'test_mode' => (bool)$this->settingsService->get('paydunya_test_mode', true),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'PayDunya';
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'paydunya';
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePayment(array $data): array
    {
        // Validation
        $validation = $this->validateRequiredFields($data, [
            'amount', 'reference', 'customer', 'return_url'
        ]);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => 'Champs requis manquants: ' . implode(', ', $validation['missing_fields'])
            ];
        }

        // Préparer les données pour PayDunya
        $payload = [
            'invoice' => [
                'total_amount' => (float)$data['amount'],
                'description' => $data['description'] ?? 'Rechargement wallet'
            ],
            'store' => [
                'name' => $this->settingsService->get('site_name', 'SunuFramework'),
                'tagline' => 'Rechargement de wallet',
                'phone' => $data['customer']['phone'] ?? '',
                'postal_address' => 'Abidjan, Côte d\'Ivoire',
                'logo_url' => url('/assets/images/logo.png')
            ],
            'actions' => [
                'cancel_url' => $data['cancel_url'] ?? $data['return_url'],
                'return_url' => $data['return_url'],
                'callback_url' => $data['webhook_url'] ?? ''
            ],
            'custom_data' => [
                'reference' => $data['reference'],
                'user_id' => $data['customer']['id'] ?? '',
                'user_email' => $data['customer']['email'] ?? ''
            ]
        ];

        $this->logInfo('Initiating PayDunya payment', ['reference' => $data['reference']]);

        // Headers PayDunya
        $headers = [
            'PAYDUNYA-MASTER-KEY: ' . $this->getConfigValue('master_key'),
            'PAYDUNYA-PRIVATE-KEY: ' . $this->getConfigValue('private_key'),
            'PAYDUNYA-TOKEN: ' . $this->getConfigValue('token'),
            'Content-Type: application/json'
        ];

        // Appel API PayDunya - Create Invoice
        $response = $this->httpRequest(
            $this->getBaseUrl() . 'checkout-invoice/create',
            'POST',
            $payload,
            $headers
        );

        if (!$response['success']) {
            $this->logError('PayDunya API call failed', [
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

        // Vérifier la réponse PayDunya
        if (isset($body['response_code']) && $body['response_code'] === '00') {
            return [
                'success' => true,
                'payment_url' => $body['response_text'], // URL de paiement
                'transaction_id' => $body['token'] ?? $data['reference'],
                'error' => null
            ];
        } else {
            $this->logError('PayDunya payment initialization failed', ['response' => $body]);

            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => $body['response_text'] ?? 'Échec de l\'initialisation du paiement'
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function verifyPayment(string $transactionId): array
    {
        $this->logInfo('Verifying PayDunya payment', ['transaction_id' => $transactionId]);

        $headers = [
            'PAYDUNYA-MASTER-KEY: ' . $this->getConfigValue('master_key'),
            'PAYDUNYA-PRIVATE-KEY: ' . $this->getConfigValue('private_key'),
            'PAYDUNYA-TOKEN: ' . $this->getConfigValue('token')
        ];

        // Appel API PayDunya - Check Invoice Status
        $response = $this->httpRequest(
            $this->getBaseUrl() . 'checkout-invoice/confirm/' . $transactionId,
            'GET',
            [],
            $headers
        );

        if (!$response['success']) {
            $this->logError('PayDunya verification failed', [
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

        // Interpréter le statut PayDunya
        if (isset($body['response_code']) && $body['response_code'] === '00') {
            $invoice = $body['invoice'] ?? [];
            $status = strtoupper($invoice['status'] ?? '');

            if ($status === 'COMPLETED') {
                return [
                    'status' => 'SUCCESS',
                    'amount' => (float)($invoice['total_amount'] ?? 0),
                    'currency' => 'XOF',
                    'transaction_id' => $transactionId,
                    'reference' => $invoice['custom_data']['reference'] ?? $transactionId,
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
                return [
                    'status' => 'FAILED',
                    'amount' => null,
                    'currency' => 'XOF',
                    'transaction_id' => $transactionId,
                    'reference' => null,
                    'message' => 'Paiement échoué ou annulé'
                ];
            }
        } else {
            return [
                'status' => 'FAILED',
                'amount' => null,
                'currency' => 'XOF',
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => $body['response_text'] ?? 'Paiement introuvable'
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleCallback(array $payload): array
    {
        $this->logInfo('Handling PayDunya callback', ['payload' => $payload]);

        $transactionId = $payload['invoice_token'] ?? $payload['token'] ?? null;

        if (!$transactionId) {
            $this->logError('PayDunya callback missing transaction ID', ['payload' => $payload]);

            return [
                'valid' => false,
                'status' => 'FAILED',
                'transaction_id' => null,
                'reference' => null,
                'amount' => null,
                'error' => 'Transaction ID manquant'
            ];
        }

        // Vérifier le paiement via l'API PayDunya (recommandé)
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
        $masterKey = $this->getConfigValue('master_key');
        $privateKey = $this->getConfigValue('private_key');
        $token = $this->getConfigValue('token');

        return !empty($masterKey) && !empty($privateKey) && !empty($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedPaymentMethods(): array
    {
        return [
            'MOBILE_MONEY', // Orange Money, MTN Money, Moov, Wave, etc.
            'CARD',         // Visa, Mastercard
            'BANK_TRANSFER' // Virement bancaire
        ];
    }

    /**
     * Obtenir l'URL de base selon le mode
     */
    private function getBaseUrl(): string
    {
        return $this->testMode ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }
}
