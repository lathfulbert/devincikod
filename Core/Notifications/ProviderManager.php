<?php

namespace App\Core\Notifications;

use App\Core\Contracts\NotificationProviderInterface;
use App\Core\Notifications\Providers\Email\SMTPProvider;
use App\Core\Notifications\Providers\Email\SendGridProvider;

/**
 * Provider Manager
 * 
 * Manages notification providers and selects appropriate provider
 */
class ProviderManager
{
    protected array $providers = [];
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->registerDefaultProviders();
    }

    /**
     * Register default providers
     */
    protected function registerDefaultProviders(): void
    {
        // Email providers
        if (!empty($this->config['providers']['smtp'])) {
            $this->register(new SMTPProvider($this->config['providers']['smtp']));
        }

        if (!empty($this->config['providers']['sendgrid'])) {
            $this->register(new SendGridProvider($this->config['providers']['sendgrid']));
        }
    }

    /**
     * Register a provider
     */
    public function register(NotificationProviderInterface $provider): void
    {
        $key = $provider->getType() . '.' . $provider->getName();
        $this->providers[$key] = $provider;
    }

    /**
     * Get provider by type and name
     */
    public function getProvider(string $type, ?string $name = null): ?NotificationProviderInterface
    {
        // If name specified, get that exact provider
        if ($name !== null) {
            $key = $type . '.' . $name;
            return $this->providers[$key] ?? null;
        }

        // Otherwise get default for type
        $defaultName = $this->config['channels'][$type]['default_provider'] ?? null;

        if ($defaultName) {
            return $this->getProvider($type, $defaultName);
        }

        // Fallback: get first healthy provider of type
        return $this->getFirstHealthyProvider($type);
    }

    /**
     * Get first healthy provider for type
     */
    protected function getFirstHealthyProvider(string $type): ?NotificationProviderInterface
    {
        foreach ($this->providers as $key => $provider) {
            if ($provider->getType() === $type && $provider->healthCheck()) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Get all providers of type
     */
    public function getProvidersForType(string $type): array
    {
        $providers = [];

        foreach ($this->providers as $provider) {
            if ($provider->getType() === $type) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * Check if provider exists
     */
    public function hasProvider(string $type, string $name): bool
    {
        $key = $type . '.' . $name;
        return isset($this->providers[$key]);
    }

    /**
     * Health check all providers
     */
    public function healthCheckAll(): array
    {
        $results = [];

        foreach ($this->providers as $key => $provider) {
            $results[$key] = $provider->healthCheck();
        }

        return $results;
    }
}
