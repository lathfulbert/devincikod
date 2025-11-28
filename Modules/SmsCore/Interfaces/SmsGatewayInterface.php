<?php

namespace Modules\SmsCore\Interfaces;

interface SmsGatewayInterface
{
    /**
     * Send an SMS message.
     *
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @param string $senderId Sender ID
     * @param array $options Additional options (e.g., scheduling, callback URL)
     * @return array Response from the gateway
     */
    public function send(string $to, string $message, string $senderId, array $options = []): array;

    /**
     * Get the balance of the gateway account.
     *
     * @return float
     */
    public function getBalance(): float;

    /**
     * Get the name of the gateway.
     *
     * @return string
     */
    public function getName(): string;
}
