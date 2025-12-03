<?php

namespace Modules\EmailMarketing\Gateways;

/**
 * Gateway SMTP générique
 * Compatible avec tout serveur SMTP (Gmail, SendGrid SMTP, Mailgun SMTP, etc.)
 */
class SmtpGateway implements EmailGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $html, array $options = []): array
    {
        try {
            $from = $options['from'] ?? $this->config['from_email'];
            $fromName = $options['from_name'] ?? $this->config['from_name'] ?? '';
            $replyTo = $options['reply_to'] ?? null;

            // Headers
            $headers = [];
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
            $headers[] = "From: {$fromName} <{$from}>";

            if ($replyTo) {
                $headers[] = "Reply-To: {$replyTo}";
            }

            // Generate unique message ID
            $messageId = uniqid('email_', true);
            $headers[] = "Message-ID: <{$messageId}@{$this->config['host']}>";

            // Utiliser PHPMailer ou Swift Mailer pour envoi SMTP réel
            // Pour l'instant, simulation
            $success = $this->sendViaSmtp($to, $subject, $html, implode("\r\n", $headers));

            if ($success) {
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'message' => 'Email sent successfully via SMTP',
                    'gateway_response' => [
                        'host' => $this->config['host'],
                        'port' => $this->config['port']
                    ]
                ];
            } else {
                throw new \Exception('SMTP send failed');
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message_id' => null,
                'message' => 'SMTP Error: ' . $e->getMessage(),
                'gateway_response' => null
            ];
        }
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
            $personalized = $html;

            // Personnalisation
            if (isset($recipient['data'])) {
                foreach ($recipient['data'] as $key => $value) {
                    $personalized = str_replace('{{' . $key . '}}', $value, $personalized);
                }
            }

            $result = $this->send($email, $subject, $personalized, $options);

            $results['results'][] = $result;

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        $results['success'] = $results['failed'] === 0;

        return $results;
    }

    public function getDeliveryStatus(string $messageId): array
    {
        // SMTP standard ne fournit pas de tracking
        return [
            'status' => 'unknown',
            'delivered_at' => null,
            'opened_at' => null
        ];
    }

    public function getCredits(): ?float
    {
        // SMTP n'a pas de système de crédits
        return null;
    }

    public function validateConfig(): bool
    {
        $required = ['host', 'port', 'username', 'password', 'from_email'];

        foreach ($required as $field) {
            if (empty($this->config[$field])) {
                return false;
            }
        }

        return true;
    }

    public function getName(): string
    {
        return 'SMTP';
    }

    /**
     * Envoi réel via SMTP (à implémenter avec PHPMailer ou Symphony Mailer)
     */
    protected function sendViaSmtp(string $to, string $subject, string $html, string $headers): bool
    {
        // TODO: Implémenter avec PHPMailer
        // require 'vendor/autoload.php';
        // $mail = new PHPMailer\PHPMailer\PHPMailer();
        // $mail->isSMTP();
        // $mail->Host = $this->config['host'];
        // $mail->Port = $this->config['port'];
        // $mail->SMTPAuth = true;
        // $mail->Username = $this->config['username'];
        // $mail->Password = $this->config['password'];
        // $mail->setFrom($this->config['from_email'], $this->config['from_name']);
        // $mail->addAddress($to);
        // $mail->isHTML(true);
        // $mail->Subject = $subject;
        // $mail->Body = $html;
        // return $mail->send();

        // Simulation pour développement
        return mail($to, $subject, $html, $headers);
    }
}
