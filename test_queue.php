<?php

require_once __DIR__ . '/preload.php';
require_once __DIR__ . '/Modules/SmsCore/Interfaces/SmsGatewayInterface.php';
require_once __DIR__ . '/Modules/SmsCore/Services/MockGateway.php';
require_once __DIR__ . '/Modules/SmsCore/Services/SmsSenderService.php';
require_once __DIR__ . '/Modules/SmsCore/Services/MessageQueueService.php';
require_once __DIR__ . '/Modules/SmsCore/Services/MessageDispatcherService.php';

use Modules\SmsCore\Services\SmsSenderService;
use Modules\SmsCore\Services\MockGateway;
use Modules\SmsCore\Services\MessageQueueService;
use Modules\SmsCore\Services\MessageDispatcherService;

try {
    echo "Testing Message Queue & Dispatcher...\n\n";

    // Setup
    $gateway = new MockGateway();
    $sender = new SmsSenderService($gateway);
    $queue = new MessageQueueService();
    $dispatcher = new MessageDispatcherService($queue, $sender);

    // Add messages to queue
    $queue->enqueue([
        'to' => '+1234567890',
        'message' => 'Hello from Queue 1',
        'sender_id' => 'TEST'
    ]);

    $queue->enqueue([
        'to' => '+0987654321',
        'message' => 'Hello from Queue 2',
        'sender_id' => 'TEST'
    ]);

    $queue->enqueue([
        'to' => '+1112223333',
        'message' => 'Hello from Queue 3',
        'sender_id' => 'TEST'
    ]);

    echo "Queue size: " . $queue->size() . "\n\n";

    // Dispatch all messages
    echo "Dispatching messages...\n";
    $results = $dispatcher->dispatch();

    foreach ($results as $result) {
        echo "Message ID: " . $result['message_id'] . " - Status: " . $result['status'] . "\n";
    }

    echo "\nAll messages dispatched successfully!\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
