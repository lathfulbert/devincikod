<?php

namespace Modules\EmailMarketing\Gateways;

/**
 * Gateway Mock pour les tests et développement
 * Simule l'envoi d'emails sans vraiment les envoyer
 */
class MockEmailGateway implements EmailGatewayInterface
{
    protected array $config;
    protected array $sentEmails = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $html, array $options = []): array
    {
        $messageId = uniqid('mock_email_', true);

        $emailData = [
            'message_id' => $messageId,
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            'options' => $options,
            'sent_at' => date('Y-m-d H:i:s')
        ];

        $this->sentEmails[] = $emailData;

        // Log dans un fichier pour debugging
        $this->logEmail($emailData);

        return [
            'success' => true,
            'message_id' => $messageId,
            'message' => 'Email sent successfully (Mock)',
            'gateway_response' => [
                'mock' => true,
                'logged_to' => 'storage/logs/mock_emails.log'
            ]
        ];
    }

    public function sendBulk(array $recipients, string $subject, string $html, array $options = []): array
    {
        $results = [
            'success' => true,
            'sent' => 0,
            'failed' => 0,
            'results' => []
        ];

        foreach ($recipients as $recipient) {
            $email = $recipient['email'];

            $result = $this->send($email, $subject, $html, $options);
            $results['results'][] = $result;
            $results['sent']++;
        }

        return $results;
    }

    public function getDeliveryStatus(string $messageId): array
    {
        return [
            'status' => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s'),
            'opened_at' => null
        ];
    }

    public function getCredits(): ?float
    {
        return 999999.0; // Crédits illimités pour le mock
    }

    public function validateConfig(): bool
    {
        return true; // Toujours valide
    }

    public function getName(): string
    {
        return 'Mock Email Gateway';
    }

    /**
     * Obtenir tous les emails envoyés (pour tests)
     */
    public function getSentEmails(): array
    {
        return $this->sentEmails;
    }

    /**
     * Réinitialiser la liste des emails envoyés
     */
    public function reset(): void
    {
        $this->sentEmails = [];
    }

    /**
     * Logger l'email dans un fichier
     */
    protected function logEmail(array $emailData): void
    {
        $logDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/storage/logs';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/mock_emails.log';

        $logEntry = sprintf(
            "[%s] Mock Email Sent\nTo: %s\nSubject: %s\nMessage ID: %s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $emailData['to'],
            $emailData['subject'],
            $emailData['message_id'],
            str_repeat('-', 80)
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
