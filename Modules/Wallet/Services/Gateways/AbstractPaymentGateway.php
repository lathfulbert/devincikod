<?php

namespace Modules\Wallet\Services\Gateways;

use Modules\Wallet\Contracts\PaymentGatewayInterface;
use Modules\Settings\Services\SettingsService;

/**
 * Abstract Payment Gateway
 *
 * Classe de base pour toutes les passerelles de paiement
 */
abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected SettingsService $settingsService;
    protected array $config;
    protected bool $testMode;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
        $this->loadConfig();
        $this->testMode = $this->getConfigValue('test_mode', true);
    }

    /**
     * Charger la configuration de la gateway
     */
    abstract protected function loadConfig(): void;

    /**
     * Récupérer une valeur de configuration
     */
    protected function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Effectuer une requête HTTP
     *
     * @param string $url
     * @param string $method GET|POST|PUT
     * @param array $data
     * @param array $headers
     * @return array [
     *   'success' => bool,
     *   'status_code' => int,
     *   'body' => array|string,
     *   'error' => string|null
     * ]
     */
    protected function httpRequest(string $url, string $method = 'GET', array $data = [], array $headers = []): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->testMode); // Désactiver en test mode

        // Method
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Headers
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'status_code' => $statusCode,
                'body' => null,
                'error' => $error
            ];
        }

        $body = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $body = $response; // Garder la réponse brute si pas JSON
        }

        return [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'body' => $body,
            'error' => null
        ];
    }

    /**
     * Logger une erreur
     */
    protected function logError(string $message, array $context = []): void
    {
        error_log(sprintf(
            "[%s Gateway] %s | Context: %s",
            $this->getName(),
            $message,
            json_encode($context)
        ));
    }

    /**
     * Logger une info
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if ($this->testMode) {
            error_log(sprintf(
                "[%s Gateway] %s | Context: %s",
                $this->getName(),
                $message,
                json_encode($context)
            ));
        }
    }

    /**
     * Générer un hash de sécurité
     */
    protected function generateHash(string $data, string $secret): string
    {
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Vérifier un hash de sécurité
     */
    protected function verifyHash(string $data, string $hash, string $secret): bool
    {
        $expectedHash = $this->generateHash($data, $secret);
        return hash_equals($expectedHash, $hash);
    }

    /**
     * Formater le montant pour la gateway (généralement en centimes)
     */
    protected function formatAmount(float $amount, bool $toCents = true): int
    {
        if ($toCents) {
            return (int)($amount * 100);
        }
        return (int)$amount;
    }

    /**
     * Convertir le montant depuis la gateway (centimes vers XOF)
     */
    protected function parseAmount(int $amount, bool $fromCents = true): float
    {
        if ($fromCents) {
            return $amount / 100;
        }
        return (float)$amount;
    }

    /**
     * Valider les données requises
     */
    protected function validateRequiredFields(array $data, array $requiredFields): array
    {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            return [
                'valid' => false,
                'missing_fields' => $missing
            ];
        }

        return ['valid' => true];
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        // Par défaut, vérifier si les clés API sont présentes
        $apiKey = $this->getConfigValue('api_key');
        $siteId = $this->getConfigValue('site_id');

        return !empty($apiKey) || !empty($siteId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedPaymentMethods(): array
    {
        // Par défaut, retourner les méthodes communes
        return ['MOBILE_MONEY', 'CARD'];
    }
}
