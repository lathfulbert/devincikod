<?php

namespace Modules\SmsCore\Services;

use Modules\SmsCore\Interfaces\SmsGatewayInterface;

class SmsSenderService
{
    protected SmsGatewayInterface $gateway;

    public function __construct(SmsGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function send(string $to, string $message, string $senderId, array $options = []): array
    {
        // Logic for validation, filtering, etc. can go here

        return $this->gateway->send($to, $message, $senderId, $options);
    }
}
