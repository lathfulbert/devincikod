<?php

namespace Modules\Wallet\Contracts;

/**
 * Payment Gateway Interface
 *
 * Interface commune pour toutes les passerelles de paiement
 */
interface PaymentGatewayInterface
{
    /**
     * Initialiser un paiement
     *
     * @param array $data [
     *   'amount' => float,
     *   'currency' => string,
     *   'reference' => string,
     *   'customer' => [
     *     'name' => string,
     *     'email' => string,
     *     'phone' => string
     *   ],
     *   'return_url' => string,
     *   'cancel_url' => string,
     *   'webhook_url' => string,
     *   'description' => string
     * ]
     * @return array [
     *   'success' => bool,
     *   'payment_url' => string|null,
     *   'transaction_id' => string|null,
     *   'error' => string|null
     * ]
     */
    public function initiatePayment(array $data): array;

    /**
     * Vérifier le statut d'un paiement
     *
     * @param string $transactionId
     * @return array [
     *   'status' => string (SUCCESS|PENDING|FAILED|CANCELLED),
     *   'amount' => float|null,
     *   'currency' => string|null,
     *   'transaction_id' => string,
     *   'reference' => string|null,
     *   'message' => string|null
     * ]
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Gérer le callback/webhook de la gateway
     *
     * @param array $payload Données reçues de la gateway
     * @return array [
     *   'valid' => bool,
     *   'status' => string,
     *   'transaction_id' => string|null,
     *   'reference' => string|null,
     *   'amount' => float|null,
     *   'error' => string|null
     * ]
     */
    public function handleCallback(array $payload): array;

    /**
     * Obtenir le nom de la gateway
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Obtenir le code unique de la gateway
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Vérifier si la gateway est configurée correctement
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Obtenir les modes de paiement supportés
     *
     * @return array ['CARD', 'MOBILE_MONEY', 'BANK_TRANSFER', etc.]
     */
    public function getSupportedPaymentMethods(): array;
}
