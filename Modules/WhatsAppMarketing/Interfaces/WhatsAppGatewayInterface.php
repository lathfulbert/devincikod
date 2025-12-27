<?php

namespace Modules\WhatsAppMarketing\Interfaces;

interface WhatsAppGatewayInterface
{
    /**
     * Send a text message
     */
    public function sendTextMessage(string $to, string $text): array;

    /**
     * Send a template message
     */
    public function sendTemplateMessage(string $to, string $templateName, string $languageCode, array $components): array;

    /**
     * Get templates from provider
     */
    public function getTemplates(): array;

    /**
     * Get gateway provider name
     */
    public function getProviderName(): string;
}
