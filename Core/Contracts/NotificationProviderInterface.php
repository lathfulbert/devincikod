<?php

namespace App\Core\Contracts;

/**
 * Notification Provider Interface
 * 
 * All notification providers must implement this interface
 */
interface NotificationProviderInterface
{
    /**
     * Send a notification
     * 
     * @param array $payload Notification data
     * @return ProviderResponse
     */
    public function send(array $payload): object;

    /**
     * Check if provider is healthy
     * 
     * @return bool
     */
    public function healthCheck(): bool;

    /**
     * Get provider type
     * 
     * @return string email, sms, push
     */
    public function getType(): string;

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getName(): string;
}
