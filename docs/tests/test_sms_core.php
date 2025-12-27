<?php

require_once __DIR__ . '/preload.php';
require_once __DIR__ . '/Modules/SmsCore/Interfaces/SmsGatewayInterface.php';
require_once __DIR__ . '/Modules/SmsCore/Services/MockGateway.php';
require_once __DIR__ . '/Modules/SmsCore/Services/SmsSenderService.php';

use Modules\SmsCore\Services\SmsSenderService;
use Modules\SmsCore\Services\MockGateway;

try {
    echo "Testing SmsCore Module...\n";

    $gateway = new MockGateway();
    $sender = new SmsSenderService($gateway);

    $response = $sender->send('+1234567890', 'Hello World', 'TestSender');

    echo "Response: " . print_r($response, true) . "\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
