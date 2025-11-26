<?php

namespace App\Core\Notifications\Providers\Email;

use App\Core\Contracts\NotificationProviderInterface;
use App\Core\Notifications\ProviderResponse;

/**
 * SMTP Email Provider
 * 
 * Sends emails using native PHP mail() or SMTP
 */
class SMTPProvider implements NotificationProviderInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Send email via SMTP
     */
    public function send(array $payload): object
    {
        try {
            $to = $payload['to'];
            $subject = $payload['subject'];
            $message = $payload['body_html'] ?? $payload['body_text'];

            // Build headers
            $headers = $this->buildHeaders($payload);

            // Send mail
            $sent = mail($to, $subject, $message, implode("\r\n", $headers));

            if ($sent) {
                return ProviderResponse::success(
                    messageId: uniqid('smtp_'),
                    metadata: ['sent_at' => time()]
                );
            }

            return ProviderResponse::failed('Failed to send email');
        } catch (\Exception $e) {
            return ProviderResponse::failed($e->getMessage());
        }
    }

    /**
     * Build email headers
     */
    protected function buildHeaders(array $payload): array
    {
        $headers = [];

        // From
        $from = $payload['from_email'] ?? $this->config['from_email'] ?? 'noreply@sunuframework.com';
        $fromName = $payload['from_name'] ?? $this->config['from_name'] ?? 'SunuFramework';
        $headers[] = "From: {$fromName} <{$from}>";

        // Content type
        if (!empty($payload['body_html'])) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }

        // Reply-To
        if (!empty($payload['reply_to'])) {
            $headers[] = "Reply-To: {$payload['reply_to']}";
        }

        return $headers;
    }

    /**
     * Health check
     */
    public function healthCheck(): bool
    {
        return function_exists('mail');
    }

    /**
     * Get provider type
     */
    public function getType(): string
    {
        return 'email';
    }

    /**
     * Get provider name
     */
    public function getName(): string
    {
        return 'smtp';
    }
}
