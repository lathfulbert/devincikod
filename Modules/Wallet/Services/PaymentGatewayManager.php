<?php

namespace Modules\Wallet\Services;

use Modules\Wallet\Contracts\PaymentGatewayInterface;
use Modules\Wallet\Services\Gateways\CinetPayGateway;
use Modules\Wallet\Services\Gateways\OrangeMoneyGateway;
use Modules\Wallet\Services\Gateways\WaveGateway;
use Modules\Wallet\Services\Gateways\PayDunyaGateway;

/**
 * Payment Gateway Manager
 *
 * Gestionnaire centralisé pour toutes les passerelles de paiement
 */
class PaymentGatewayManager
{
    private array $gateways = [];
    private array $registeredGateways = [
        'cinetpay' => CinetPayGateway::class,
        'orange_money' => OrangeMoneyGateway::class,
        'wave' => WaveGateway::class,
        'paydunya' => PayDunyaGateway::class,
    ];

    public function __construct()
    {
        $this->loadGateways();
    }

    /**
     * Charger toutes les gateways disponibles
     */
    private function loadGateways(): void
    {
        foreach ($this->registeredGateways as $code => $class) {
            try {
                $gateway = new $class();
                $this->gateways[$code] = $gateway;
            } catch (\Exception $e) {
                error_log("Failed to load gateway {$code}: " . $e->getMessage());
            }
        }
    }

    /**
     * Récupérer une gateway par son code
     *
     * @param string $code
     * @return PaymentGatewayInterface|null
     */
    public function getGateway(string $code): ?PaymentGatewayInterface
    {
        return $this->gateways[$code] ?? null;
    }

    /**
     * Récupérer toutes les gateways disponibles
     *
     * @return array
     */
    public function getAllGateways(): array
    {
        return $this->gateways;
    }

    /**
     * Récupérer les gateways configurées et actives
     *
     * @return array
     */
    public function getActiveGateways(): array
    {
        return array_filter($this->gateways, function($gateway) {
            return $gateway->isConfigured();
        });
    }

    /**
     * Récupérer les informations de toutes les gateways
     *
     * @return array [
     *   ['code' => string, 'name' => string, 'configured' => bool, 'methods' => array],
     *   ...
     * ]
     */
    public function getGatewaysInfo(): array
    {
        $info = [];

        foreach ($this->gateways as $code => $gateway) {
            $info[] = [
                'code' => $gateway->getCode(),
                'name' => $gateway->getName(),
                'configured' => $gateway->isConfigured(),
                'supported_methods' => $gateway->getSupportedPaymentMethods(),
            ];
        }

        return $info;
    }

    /**
     * Initier un paiement avec une gateway spécifique
     *
     * @param string $gatewayCode
     * @param array $data
     * @return array
     */
    public function initiatePayment(string $gatewayCode, array $data): array
    {
        $gateway = $this->getGateway($gatewayCode);

        if (!$gateway) {
            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => 'Passerelle de paiement introuvable: ' . $gatewayCode
            ];
        }

        if (!$gateway->isConfigured()) {
            return [
                'success' => false,
                'payment_url' => null,
                'transaction_id' => null,
                'error' => 'La passerelle ' . $gateway->getName() . ' n\'est pas configurée'
            ];
        }

        return $gateway->initiatePayment($data);
    }

    /**
     * Vérifier le statut d'un paiement
     *
     * @param string $gatewayCode
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment(string $gatewayCode, string $transactionId): array
    {
        $gateway = $this->getGateway($gatewayCode);

        if (!$gateway) {
            return [
                'status' => 'FAILED',
                'amount' => null,
                'currency' => null,
                'transaction_id' => $transactionId,
                'reference' => null,
                'message' => 'Passerelle introuvable'
            ];
        }

        return $gateway->verifyPayment($transactionId);
    }

    /**
     * Gérer un callback de gateway
     *
     * @param string $gatewayCode
     * @param array $payload
     * @return array
     */
    public function handleCallback(string $gatewayCode, array $payload): array
    {
        $gateway = $this->getGateway($gatewayCode);

        if (!$gateway) {
            return [
                'valid' => false,
                'status' => 'FAILED',
                'transaction_id' => null,
                'reference' => null,
                'amount' => null,
                'error' => 'Passerelle introuvable'
            ];
        }

        return $gateway->handleCallback($payload);
    }

    /**
     * Enregistrer une nouvelle gateway
     *
     * @param string $code
     * @param string $className
     * @return bool
     */
    public function registerGateway(string $code, string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        try {
            $gateway = new $className();
            if (!($gateway instanceof PaymentGatewayInterface)) {
                return false;
            }

            $this->registeredGateways[$code] = $className;
            $this->gateways[$code] = $gateway;

            return true;
        } catch (\Exception $e) {
            error_log("Failed to register gateway {$code}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si une gateway est disponible et configurée
     *
     * @param string $code
     * @return bool
     */
    public function isGatewayAvailable(string $code): bool
    {
        $gateway = $this->getGateway($code);
        return $gateway && $gateway->isConfigured();
    }

    /**
     * Obtenir le code de la gateway par défaut
     *
     * @return string|null
     */
    public function getDefaultGatewayCode(): ?string
    {
        $activeGateways = $this->getActiveGateways();

        if (empty($activeGateways)) {
            return null;
        }

        // Retourner la première gateway active
        return array_key_first($activeGateways);
    }

    /**
     * Obtenir la gateway par défaut
     *
     * @return PaymentGatewayInterface|null
     */
    public function getDefaultGateway(): ?PaymentGatewayInterface
    {
        $code = $this->getDefaultGatewayCode();
        return $code ? $this->getGateway($code) : null;
    }
}
