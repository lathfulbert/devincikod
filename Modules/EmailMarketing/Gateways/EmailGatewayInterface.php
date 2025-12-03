<?php

namespace Modules\EmailMarketing\Gateways;

/**
 * Interface pour tous les gateways d'envoi d'emails
 */
interface EmailGatewayInterface
{
    /**
     * Envoyer un email unique
     *
     * @param string $to Email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $html Contenu HTML
     * @param array $options Options additionnelles (from, reply_to, attachments, etc.)
     * @return array ['success' => bool, 'message_id' => string, 'message' => string, 'gateway_response' => array]
     */
    public function send(
        string $to,
        string $subject,
        string $html,
        array $options = []
    ): array;

    /**
     * Envoyer des emails en masse
     *
     * @param array $recipients [['email' => 'test@example.com', 'name' => 'Test', 'data' => [...]]]
     * @param string $subject
     * @param string $html
     * @param array $options
     * @return array ['success' => bool, 'sent' => int, 'failed' => int, 'results' => array]
     */
    public function sendBulk(
        array $recipients,
        string $subject,
        string $html,
        array $options = []
    ): array;

    /**
     * Obtenir le statut de livraison d'un message
     *
     * @param string $messageId ID du message
     * @return array ['status' => string, 'delivered_at' => string|null, 'opened_at' => string|null]
     */
    public function getDeliveryStatus(string $messageId): array;

    /**
     * Obtenir les crédits restants (si applicable)
     *
     * @return float|null Nombre de crédits ou null si non applicable
     */
    public function getCredits(): ?float;

    /**
     * Valider la configuration du gateway
     *
     * @return bool
     */
    public function validateConfig(): bool;

    /**
     * Obtenir le nom du gateway
     *
     * @return string
     */
    public function getName(): string;
}
