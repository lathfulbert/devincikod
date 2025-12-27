<?php

require_once __DIR__ . '/preload.php';
require_once __DIR__ . '/Modules/SmsCore/Interfaces/SmsGatewayInterface.php';
require_once __DIR__ . '/Modules/SmsCore/Services/GatewayFactory.php';
require_once __DIR__ . '/Modules/SmsCore/Gateways/InfobipGateway.php';
require_once __DIR__ . '/Modules/SmsCore/Gateways/OrangeSmsGateway.php';
require_once __DIR__ . '/Modules/SmsCore/Services/MockGateway.php';

use Modules\SmsCore\Services\GatewayFactory;

try {
    echo "Testing Gateway Factory & Real Gateways...\n\n";

    // Load gateway config
    $gatewayConfig = require __DIR__ . '/config/gateways.php';

    // Create factory
    $factory = new GatewayFactory();

    // Register all gateways
    foreach ($gatewayConfig as $name => $config) {
        $factory->registerConfiguration($name, $config);
    }

    echo "Available gateways: " . implode(', ', $factory->getAvailableGateways()) . "\n\n";

    // Test creating gateways
    echo "===== Testing Gateway Creation =====\n";

    // Mock gateway (should work)
    echo "Creating Mock gateway...\n";
    $mockGateway = $factory->create('mock');
    echo "✓ Mock gateway created: " . $mockGateway->getName() . "\n";
    echo "  Balance: $" . $mockGateway->getBalance() . "\n\n";

    // Infobip gateway (will fail without credentials, but proves structure works)
    echo "Creating Infobip gateway...\n";
    try {
        $infobipGateway = $factory->create('infobip');
        echo "✓ Infobip gateway created: " . $infobipGateway->getName() . "\n";
        echo "  Note: Actual sending requires valid API key\n\n";
    } catch (\Throwable $e) {
        echo "✗ Infobip error (expected without credentials): " . $e->getMessage() . "\n\n";
    }

    // Orange gateway
    echo "Creating Orange SMS gateway...\n";
    try {
        $orangeGateway = $factory->create('orange');
        echo "✓ Orange SMS gateway created: " . $orangeGateway->getName() . "\n";
        echo "  Note: Actual sending requires valid credentials\n\n";
    } catch (\Throwable $e) {
        echo "✗ Orange error (expected without credentials): " . $e->getMessage() . "\n\n";
    }

    // Test mock gateway sending
    echo "===== Testing Mock Gateway =====\n";
    $response = $mockGateway->send('+1234567890', 'Test message', 'TestSender');
    echo "Send result: " . print_r($response, true) . "\n";

    echo "\n✓ Gateway factory tests completed!\n";
    echo "\nTo use real gateways, configure credentials in .env file:\n";
    echo "  - INFOBIP_API_KEY=your_key\n";
    echo "  - ORANGE_CLIENT_ID=your_id\n";
    echo "  - ORANGE_CLIENT_SECRET=your_secret\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
