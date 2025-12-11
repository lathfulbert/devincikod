<?php

namespace Modules\Wallet\Services\Gateways;

/**
 * CinetPay Gateway
 *
 * Intégration avec CinetPay (Côte d'Ivoire, Sénégal, Mali, etc.)
 * Documentation: https://docs.cinetpay.com
 */
class CinetPayGateway extends AbstractPaymentGateway
{
    private const SANDBOX_URL = 'https://api-checkout.cinetpay.com/v2/';
    private const PRODUCTION_URL = 'https://api-checkout.cinetpay.com/v2/';

    /**
     * {@inheritdoc}
     */
    protected function loadConfig(): void
    {
        $this->config = [
            'api_key' => $this->settingsService->get('cinetpay_api_key', ''),
            'site_id' => $this->settingsService->get('cinetpay_site_id', ''),
            'secret_key' => $this->settingsService->get('cinetpay_secret_key', ''),
            'test_mode' => (bool)$this->settingsService->get('cinetpay_test_mode', true),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'CinetPay';
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'cinetpay';
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePayment(array $data): array
    {
        // Validation
        $validation = $this->validateRequiredFields($data, [
            'amount', 'currency', 'reference', 'customer', 'return_url', 'webhook_url'
        ]);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => 'Champs requis manquants: ' . implode(', ', $validation['missing_fields'])
            ];
        }

        // Préparer les données pour CinetPay
        $payload = [
            'apikey' => $this->getConfigValue('api_key'),
            'site_id' => $this->getConfigValue('site_id'),
            'transaction_id' => $data['reference'], // ID unique de transaction
            'amount' => (int)$data['amount'], // CinetPay utilise le montant entier
            'currency' => $data['currency'], // XOF, XAF, etc.
            'description' => $data['description'] ?? 'Rechargement wallet',
            'customer_name' => $data['customer']['name'] ?? '',
            'customer_surname' => $data['customer']['name'] ?? '',
            'customer_email' => $data['customer']['email'] ?? '',
            'customer_phone_number' => $data['customer']['phone'] ?? '',
            'customer_address' => $data['customer']['address'] ?? '',
            'customer_city' => $data['customer']['city'] ?? 'Abidjan',
            'customer_country' => $data['customer']['country'] ?? 'CI',
            'customer_state' => $data['customer']['state'] ?? 'CI',
            'customer_zip_code' => $data['customer']['zip'] ?? '00225',
            'return_url' => $data['return_url'],
            'notify_url' => $data['webhook_url'],
            'channels' => 'ALL', // ALL, MOBILE_MONEY, CREDIT_CARD, etc.
            'metadata' => $data['metadata'] ?? '',
            'lang' => 'fr',
            'invoice_data' => []
        ];

        $this->logInfo('Initiating CinetPay payment', ['transaction_id' => $data['reference']]);

        // Appel API CinetPay
        $response = $this->httpRequest(
            $this->getBaseUrl() . 'payment',
            'POST',
            $payload
        );

        if (!$response['success']) {
            $this->logError('CinetPay API call failed', [
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

        // Vérifier la réponse CinetPay
        if (isset($body['code']) && $body['code'] === '201') {
            // Succès
            return [
                'success' => true,
                'payment_url' => $body['data']['payment_url'] ?? null,
                'transaction_id' => $body['data']['payment_token'] ?? $data['reference'],
                'error' => null
            ];
        } else {
            // Échec
            $this->logError('CinetPay payment initialization failed', ['response' => $body]);

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
        $payload = [
            'apikey' => $this->getConfigValue('api_key'),
            'site_id' => $this->getConfigValue('site_id'),
            'transaction_id' => $transactionId
        ];

        $this->logInfo('Verifying CinetPay payment', ['transaction_id' => $transactionId]);

        $response = $this->httpRequest(
            $this->getBaseUrl() . 'payment/check',
            'POST',
            $payload
        );

        if (!$response['success']) {
            $this->logError('CinetPay verification failed', [
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

        // Interpréter le statut CinetPay
        if (isset($body['code']) && $body['code'] === '00') {
            // Paiement réussi
            $paymentData = $body['data'] ?? [];

            return [
                'status' => 'SUCCESS',
                'amount' => (float)($paymentData['amount'] ?? 0),
                'currency' => $paymentData['currency'] ?? 'XOF',
                'transaction_id' => $transactionId,
                'reference' => $paymentData['metadata'] ?? $transactionId,
                'message' => $paymentData['payment_method'] ?? 'Paiement confirmé'
            ];
        } elseif (isset($body['code']) && $body['code'] === '629') {
            // Paiement en attente
            return [
                'status' => 'PENDING',
                'amount' => null,
                'currency' => 'XOF',
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => 'Paiement en attente'
            ];
        } else {
            // Paiement échoué ou annulé
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
        $this->logInfo('Handling CinetPay callback', ['payload' => $payload]);

        // Vérifier la signature (si CinetPay l'envoie)
        // Note: CinetPay n'utilise pas de signature HMAC, vérifier via API

        $transactionId = $payload['cpm_trans_id'] ?? $payload['transaction_id'] ?? null;

        if (!$transactionId) {
            $this->logError('CinetPay callback missing transaction ID', ['payload' => $payload]);

            return [
                'valid' => false,
                'status' => 'FAILED',
                'transaction_id' => null,
                'reference' => null,
                'amount' => null,
                'error' => 'Transaction ID manquant'
            ];
        }

        // Vérifier le paiement via l'API CinetPay (recommandé)
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
        $apiKey = $this->getConfigValue('api_key');
        $siteId = $this->getConfigValue('site_id');

        return !empty($apiKey) && !empty($siteId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedPaymentMethods(): array
    {
        return [
            'MOBILE_MONEY', // Orange Money, MTN Money, Moov Money, etc.
            'CARD',         // Visa, Mastercard
            'FLOOZ',        // Moov Money (Bénin, Togo)
            'TMONEY',       // Togocel (Togo)
            'CREDIT_CARD'   // Cartes bancaires
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
