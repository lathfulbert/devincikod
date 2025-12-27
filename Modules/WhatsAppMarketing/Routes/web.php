<?php

use App\Core\Middleware\AuthMiddleware;

$authMiddleware = [new AuthMiddleware(), 'handle'];

return [
    // Dashboard
    ['GET', '/admin/whatsapp', [\Modules\WhatsAppMarketing\Controllers\WhatsAppDashboardController::class, 'index'], [$authMiddleware]],

    // Gateways
    ['GET', '/admin/whatsapp/gateways', [\Modules\WhatsAppMarketing\Controllers\WhatsAppGatewayController::class, 'index'], [$authMiddleware]],
    ['GET', '/admin/whatsapp/gateways/create', [\Modules\WhatsAppMarketing\Controllers\WhatsAppGatewayController::class, 'create'], [$authMiddleware]],
    ['POST', '/admin/whatsapp/gateways/store', [\Modules\WhatsAppMarketing\Controllers\WhatsAppGatewayController::class, 'store'], [$authMiddleware]],
    ['GET', '/admin/whatsapp/gateways/{id}/edit', [\Modules\WhatsAppMarketing\Controllers\WhatsAppGatewayController::class, 'edit'], [$authMiddleware]],
    ['POST', '/admin/whatsapp/gateways/{id}/update', [\Modules\WhatsAppMarketing\Controllers\WhatsAppGatewayController::class, 'update'], [$authMiddleware]],
    ['POST', '/admin/whatsapp/gateways/{id}/delete', [\Modules\WhatsAppMarketing\Controllers\WhatsAppGatewayController::class, 'delete'], [$authMiddleware]],

    // Templates
    ['GET', '/admin/whatsapp/templates', [\Modules\WhatsAppMarketing\Controllers\WhatsAppTemplateController::class, 'index'], [$authMiddleware]],
    ['GET', '/admin/whatsapp/templates/create', [\Modules\WhatsAppMarketing\Controllers\WhatsAppTemplateController::class, 'create'], [$authMiddleware]],
    ['POST', '/admin/whatsapp/templates/store', [\Modules\WhatsAppMarketing\Controllers\WhatsAppTemplateController::class, 'store'], [$authMiddleware]],
    ['GET', '/admin/whatsapp/templates/{id}/sync', [\Modules\WhatsAppMarketing\Controllers\WhatsAppTemplateController::class, 'sync'], [$authMiddleware]], // Sync with provider

    // Campaigns
    ['GET', '/admin/whatsapp/campaigns', [\Modules\WhatsAppMarketing\Controllers\WhatsAppCampaignController::class, 'index'], [$authMiddleware]],
    ['GET', '/admin/whatsapp/campaigns/create', [\Modules\WhatsAppMarketing\Controllers\WhatsAppCampaignController::class, 'create'], [$authMiddleware]],
    ['POST', '/admin/whatsapp/campaigns/store', [\Modules\WhatsAppMarketing\Controllers\WhatsAppCampaignController::class, 'store'], [$authMiddleware]],

    // Messages / Conversations (simplified)
    ['GET', '/admin/whatsapp/messages', [\Modules\WhatsAppMarketing\Controllers\WhatsAppMessageController::class, 'index'], [$authMiddleware]],

    // Webhooks (Public routes)
    ['POST', '/api/v1/whatsapp/webhook/{gateway_id}', [\Modules\WhatsAppMarketing\Controllers\WhatsAppWebhookController::class, 'handle'], []],
];
