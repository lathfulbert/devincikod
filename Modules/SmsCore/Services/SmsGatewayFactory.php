<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;
use Modules\SmsCore\Gateways\OrangeCIGateway;
use Modules\SmsCore\Gateways\InfobipGateway;
use Modules\SmsCore\Gateways\MockGateway;
use Modules\Settings\Models\SmsGateway;

class SmsGatewayFactory
{
    /**
     * Create gateway instance based on provider code
     *
     * Si les credentials API sont vides ou invalides, retourne un MockGateway pour tester
     */
    public static function create(SmsGateway $config): ?SmsGatewayInterface
    {
        // Si pas de credentials configurées ou trop courtes (probablement invalides)
        // utiliser le MockGateway pour tester sans vraies APIs
        $hasValidApiKey = !empty($config->api_key) && strlen(trim($config->api_key)) > 10;
        $hasValidApiSecret = !empty($config->api_secret) && strlen(trim($config->api_secret)) > 10;

        if (!$hasValidApiKey || !$hasValidApiSecret) {
            return new MockGateway($config);
        }

        switch ($config->provider_code) {
            case 'orange_ci':
                return new OrangeCIGateway($config);

            case 'infobip':
                return new InfobipGateway($config);

            default:
                return new MockGateway($config);
        }
    }
}
