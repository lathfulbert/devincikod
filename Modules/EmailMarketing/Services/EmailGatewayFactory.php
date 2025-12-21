<?php

namespace Modules\EmailMarketing\Services;

use Modules\EmailMarketing\Gateways\EmailGatewayInterface;
use Modules\EmailMarketing\Gateways\SmtpGateway;
use Modules\EmailMarketing\Gateways\MockEmailGateway;
use Modules\EmailMarketing\Gateways\TwilioEmailGateway;
use Modules\EmailMarketing\Gateways\InfobipEmailGateway;
use Modules\EmailMarketing\Gateways\ElasticMailGateway;

/**
 * Factory pour créer des instances de gateways email
 */
class EmailGatewayFactory
{
    protected array $config;
    protected array $instances = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Créer ou récupérer une instance de gateway
     *
     * @param string|null $gatewayName Nom du gateway ou null pour le défaut
     * @return EmailGatewayInterface
     * @throws \Exception
     */
    public function make(?string $gatewayName = null): EmailGatewayInterface
    {
        $gatewayName = $gatewayName ?? $this->getDefaultGateway();

        // Retourner l'instance si déjà créée (singleton)
        if (isset($this->instances[$gatewayName])) {
            return $this->instances[$gatewayName];
        }

        // Récupérer la configuration du gateway
        $gatewayConfig = $this->getGatewayConfig($gatewayName);

        // Créer l'instance
        $gateway = $this->createGateway($gatewayName, $gatewayConfig);

        // Valider la configuration
        if (!$gateway->validateConfig()) {
            throw new \Exception("Invalid configuration for gateway: {$gatewayName}");
        }

        // Stocker l'instance
        $this->instances[$gatewayName] = $gateway;

        return $gateway;
    }

    /**
     * Créer une instance de gateway selon le type
     *
     * @param string $gatewayName
     * @param array $config
     * @return EmailGatewayInterface
     * @throws \Exception
     */
    protected function createGateway(string $gatewayName, array $config): EmailGatewayInterface
    {
        return match (strtolower($gatewayName)) {
            'smtp' => new SmtpGateway($config),
            'mock' => new MockEmailGateway($config),
            'twilio', 'sendgrid' => new TwilioEmailGateway($config),
            'infobip' => new InfobipEmailGateway($config),
            'elasticmail' => new ElasticMailGateway($config),
            default => throw new \Exception("Unknown email gateway: {$gatewayName}")
        };
    }

    /**
     * Obtenir la configuration d'un gateway
     *
     * @param string $gatewayName
     * @return array
     * @throws \Exception
     */
    protected function getGatewayConfig(string $gatewayName): array
    {
        // Essayer de charger depuis la config de l'application
        if (empty($this->config)) {
            $this->loadConfigFromApp();
        }

        if (!isset($this->config['gateways'][$gatewayName])) {
            throw new \Exception("Gateway configuration not found: {$gatewayName}");
        }

        return $this->config['gateways'][$gatewayName];
    }

    /**
     * Charger la configuration depuis l'application
     */
    protected function loadConfigFromApp(): void
    {
        try {
            $app = \App\Core\Application::getInstance();
            $mailConfig = $app->config->get('mail', []);

            $this->config = [
                'default' => $mailConfig['default_gateway'] ?? 'mock',
                'gateways' => $mailConfig['gateways'] ?? $this->getDefaultConfig()
            ];
        } catch (\Exception $e) {
            // Si l'application n'est pas initialisée, utiliser la config par défaut
            $this->config = [
                'default' => 'mock',
                'gateways' => $this->getDefaultConfig()
            ];
        }
    }

    /**
     * Configuration par défaut
     */
    protected function getDefaultConfig(): array
    {
        return [
            'mock' => [
                'driver' => 'mock'
            ],
            'smtp' => [
                'driver' => 'smtp',
                'host' => getenv('MAIL_HOST') ?: 'localhost',
                'port' => getenv('MAIL_PORT') ?: 587,
                'username' => getenv('MAIL_USERNAME') ?: '',
                'password' => getenv('MAIL_PASSWORD') ?: '',
                'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
                'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com',
                'from_name' => getenv('MAIL_FROM_NAME') ?: 'App'
            ]
        ];
    }

    /**
     * Obtenir le gateway par défaut
     */
    protected function getDefaultGateway(): string
    {
        return $this->config['default'] ?? 'mock';
    }

    /**
     * Obtenir le gateway par défaut (public)
     */
    public function getDefaultGatewayPublic(): string
    {
        return $this->getDefaultGateway();
    }

    /**
     * Obtenir tous les gateways disponibles
     *
     * @return array
     */
    public function getAvailableGateways(): array
    {
        if (empty($this->config)) {
            $this->loadConfigFromApp();
        }

        return array_keys($this->config['gateways'] ?? []);
    }

    /**
     * Tester un gateway
     *
     * @param string $gatewayName
     * @return array ['success' => bool, 'message' => string]
     */
    public function testGateway(string $gatewayName): array
    {
        try {
            $gateway = $this->make($gatewayName);

            // Test d'envoi simple
            $result = $gateway->send(
                'test@example.com',
                'Test Email',
                '<p>This is a test email</p>',
                ['from' => 'noreply@example.com']
            );

            return [
                'success' => $result['success'],
                'message' => $result['success']
                    ? "Gateway {$gatewayName} is working properly"
                    : "Gateway {$gatewayName} failed: {$result['message']}"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Gateway {$gatewayName} error: {$e->getMessage()}"
            ];
        }
    }

    /**
     * Changer le gateway par défaut
     */
    public function setDefaultGateway(string $gatewayName): void
    {
        $this->config['default'] = $gatewayName;
    }

    /**
     * Réinitialiser les instances (utile pour tests)
     */
    public function reset(): void
    {
        $this->instances = [];
    }

    /**
     * Obtenir la configuration d'un gateway (public)
     */
    public function getGatewayConfigPublic(string $gatewayName): array
    {
        return $this->getGatewayConfig($gatewayName);
    }
}
