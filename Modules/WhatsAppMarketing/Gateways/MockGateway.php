<?php

namespace Modules\WhatsAppMarketing\Gateways;

use Modules\WhatsAppMarketing\Interfaces\WhatsAppGatewayInterface;

class MockGateway implements WhatsAppGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sendTextMessage(string $to, string $text): array
    {
        return [
            'success' => true,
            'message_id' => 'mock_msg_' . uniqid(),
            'status' => 'queued',
            'cost' => 0.05
        ];
    }

    public function sendTemplateMessage(string $to, string $templateName, string $languageCode, array $components): array
    {
        return [
            'success' => true,
            'message_id' => 'mock_tmpl_' . uniqid(),
            'status' => 'queued',
            'cost' => 0.08
        ];
    }

    public function getTemplates(): array
    {
        return [
            [
                'name' => 'hello_world',
                'language' => 'en_US',
                'status' => 'APPROVED',
                'category' => 'MARKETING',
                'id' => '123'
            ],
            [
                'name' => 'order_update',
                'language' => 'fr',
                'status' => 'APPROVED',
                'category' => 'UTILITY',
                'id' => '456'
            ]
        ];
    }

    public function getProviderName(): string
    {
        return 'mock';
    }
}
