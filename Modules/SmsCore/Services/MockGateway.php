<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class MockGateway implements SmsGatewayInterface
{
    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        return [
            'status' => 'success',
            'message_id' => uniqid('mock_'),
            'to' => $to,
            'content' => $message
        ];
    }

    public function getBalance(): float
    {
        return 100.00;
    }

    public function getName(): string
    {
        return 'MockGateway';
    }
}
