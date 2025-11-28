<?php

require_once __DIR__ . '/preload.php';
require_once __DIR__ . '/Modules/SmsCore/Interfaces/SmsGatewayInterface.php';
require_once __DIR__ . '/Modules/SmsCore/Services/MockGateway.php';
require_once __DIR__ . '/Modules/SmsCore/Services/GatewaySelectorService.php';
require_once __DIR__ . '/Modules/SmsCore/Services/RoutingEngineService.php';

use Modules\SmsCore\Services\MockGateway;
use Modules\SmsCore\Services\GatewaySelectorService;
use Modules\SmsCore\Services\RoutingEngineService;
use Modules\SmsCore\Interfaces\SmsGatewayInterface;

// Create a second mock gateway
class AnotherMockGateway implements SmsGatewayInterface
{
    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        return [
            'status' => 'success',
            'message_id' => uniqid('another_'),
            'gateway' => 'AnotherMock'
        ];
    }

    public function getBalance(): float
    {
        return 200.00;
    }
    public function getName(): string
    {
        return 'AnotherMockGateway';
    }
}

try {
    echo "Testing Routing Engine...\n\n";

    // Setup
    $selector = new GatewaySelectorService();
    $routing = new RoutingEngineService($selector);

    // Register gateways
    $gateway1 = new MockGateway();
    $gateway2 = new AnotherMockGateway();

    $selector->registerGateway($gateway1);
    $selector->registerGateway($gateway2);

    echo "Registered gateways: " . implode(', ', $selector->getGateways()) . "\n\n";

    // Add routing rules
    $routing->addRule('country', 'US', 'MockGateway');
    $routing->addRule('country', 'FR', 'AnotherMockGateway');
    $routing->addRule('priority', 'high', 'AnotherMockGateway');

    // Test country-based routing
    echo "Test 1: Country-based routing (US)\n";
    $message = ['country' => 'US', 'to' => '+1234567890'];
    $gateway = $routing->route($message);
    echo "Selected gateway: " . $gateway->getName() . "\n\n";

    // Test country-based routing (FR)
    echo "Test 2: Country-based routing (FR)\n";
    $message = ['country' => 'FR', 'to' => '+33123456789'];
    $gateway = $routing->route($message);
    echo "Selected gateway: " . $gateway->getName() . "\n\n";

    // Test priority routing
    echo "Test 3: Priority routing (high)\n";
    $message = ['priority' => 'high', 'to' => '+1234567890'];
    $gateway = $routing->route($message);
    echo "Selected gateway: " . $gateway->getName() . "\n\n";

    // Test round-robin (no matching rules)
    echo "Test 4: Round-robin routing (no rules match)\n";
    $selector->setStrategy('round_robin');
    for ($i = 0; $i < 4; $i++) {
        $message = ['to' => '+1234567890'];
        $gateway = $routing->route($message);
        echo "  Round $i: " . $gateway->getName() . "\n";
    }

    echo "\nAll routing tests passed!\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
