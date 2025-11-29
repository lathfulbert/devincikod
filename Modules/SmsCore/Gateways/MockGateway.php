<?php

namespace Modules\SmsCore\Gateways;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;
use Modules\Settings\Models\SmsGateway;

/**
 * Mock Gateway pour les tests sans vraies API
 *
 * Ce gateway simule l'envoi de SMS sans appeler de vraies APIs.
 * Parfait pour le développement et les tests.
 */
class MockGateway implements SmsGatewayInterface
{
    private SmsGateway $config;

    public function __construct(SmsGateway $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        // Simuler un délai réseau
        usleep(500000); // 0.5 secondes

        // Générer un faux message ID
        $fakeMessageId = 'MOCK-' . strtoupper(uniqid());

        // Simuler une réponse d'API réussie
        $mockResponse = [
            'status' => 'success',
            'messageId' => $fakeMessageId,
            'to' => $to,
            'from' => $senderId,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'gateway' => $this->config->provider_code,
            'cost' => 0.05, // Coût fictif
            'mock' => true // Indicateur que c'est un mock
        ];

        // Simuler des échecs aléatoires (10% de chance)
        if (rand(1, 100) <= 10) {
            return [
                'success' => false,
                'message' => 'Mock failure: Numéro invalide (simulation)',
                'error' => 'Invalid phone number format',
                'gateway_response' => [
                    'error' => 'MOCK_ERROR',
                    'description' => 'Ceci est une erreur simulée pour tester la gestion d\'erreurs',
                    'timestamp' => date('Y-m-d H:i:s')
                ],
                'sent_at' => date('Y-m-d H:i:s')
            ];
        }

        // Retourner un succès
        return [
            'success' => true,
            'message' => 'Mock SMS sent successfully',
            'gateway_message_id' => $fakeMessageId,
            'gateway_response' => $mockResponse,
            'sent_at' => date('Y-m-d H:i:s')
        ];
    }

    public function getBalance(): float
    {
        // Retourner un solde fictif
        return 999.99;
    }

    public function getName(): string
    {
        return $this->config->name . ' (MOCK)';
    }
}
