<?php

namespace Modules\WhatsAppMarketing\Services;

use Modules\WhatsAppMarketing\Models\WhatsAppGateway;
use Modules\WhatsAppMarketing\Interfaces\WhatsAppGatewayInterface;
use Modules\WhatsAppMarketing\Gateways\MockGateway;

class WhatsAppGatewayFactory
{
    public static function create(WhatsAppGateway $gateway): ?WhatsAppGatewayInterface
    {
        $credentials = json_decode($gateway->credentials, true) ?? [];

        // Decrypt credentials if needed (placeholder)
        // $credentials = ...

        switch ($gateway->provider) {
            case 'mock':
                return new MockGateway($credentials);
            case 'twilio':
                // return new TwilioWhatsAppGateway($credentials);
            case 'wati':
                // return new WatiGateway($credentials);
            default:
                throw new \Exception("Unsupported WhatsApp provider: {$gateway->provider}");
        }
    }
}
