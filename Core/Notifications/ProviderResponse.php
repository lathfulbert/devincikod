<?php

namespace App\Core\Notifications;

/**
 * Provider Response
 * 
 * Standardized response from notification providers
 */
class ProviderResponse
{
    public function __construct(
        public bool $success,
        public ?string $messageId = null,
        public ?string $error = null,
        public array $metadata = []
    ) {}

    /**
     * Create successful response
     */
    public static function success(string $messageId = null, array $metadata = []): self
    {
        return new self(true, $messageId, null, $metadata);
    }

    /**
     * Create failed response
     */
    public static function failed(string $error, array $metadata = []): self
    {
        return new self(false, null, $error, $metadata);
    }

    /**
     * Check if response is successful
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Check if response failed
     */
    public function isFailed(): bool
    {
        return !$this->success;
    }
}
