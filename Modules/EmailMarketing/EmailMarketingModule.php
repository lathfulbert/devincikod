<?php

namespace Modules\EmailMarketing;

use App\Core\Module\AbstractModule;

class EmailMarketingModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // Dashboard
            ['GET', '/admin/email-marketing', [\Modules\EmailMarketing\Controllers\DashboardController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/statistics', [\Modules\EmailMarketing\Controllers\DashboardController::class, 'statistics'], [$authMiddleware]],

            // Campaigns
            ['GET', '/admin/email-marketing/campaigns', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/campaigns/create', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/campaigns/store', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/campaigns/{id}', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'show'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/campaigns/{id}/edit', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/campaigns/{id}/update', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/campaigns/{id}/send', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'send'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/campaigns/{id}/pause', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'pause'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/campaigns/{id}/resume', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'resume'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/campaigns/{id}/delete', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'delete'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/campaigns/{id}/analytics', [\Modules\EmailMarketing\Controllers\EmailCampaignController::class, 'analytics'], [$authMiddleware]],

            // Templates
            ['GET', '/admin/email-marketing/templates', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/templates/create', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/templates/store', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/templates/{id}', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'show'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/templates/{id}/edit', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/templates/{id}/update', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/templates/{id}/duplicate', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'duplicate'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/templates/{id}/delete', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'delete'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/templates/{id}/test', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'sendTest'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/templates/{id}/preview', [\Modules\EmailMarketing\Controllers\EmailTemplateController::class, 'preview'], [$authMiddleware]],

            // Workflows
            ['GET', '/admin/email-marketing/workflows', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/workflows/create', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/workflows/store', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/workflows/{id}', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'show'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/workflows/{id}/edit', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/workflows/{id}/update', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/workflows/{id}/activate', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'activate'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/workflows/{id}/pause', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'pause'], [$authMiddleware]],
            ['POST', '/admin/email-marketing/workflows/{id}/execute', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'execute'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/workflows/{id}/delete', [\Modules\EmailMarketing\Controllers\WorkflowController::class, 'delete'], [$authMiddleware]],

            // Analytics
            ['GET', '/admin/email-marketing/analytics', [\Modules\EmailMarketing\Controllers\AnalyticsController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/analytics/campaign/{id}', [\Modules\EmailMarketing\Controllers\AnalyticsController::class, 'campaign'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/analytics/compare', [\Modules\EmailMarketing\Controllers\AnalyticsController::class, 'compare'], [$authMiddleware]],
            ['GET', '/admin/email-marketing/analytics/multichannel/{id}', [\Modules\EmailMarketing\Controllers\AnalyticsController::class, 'multichannel'], [$authMiddleware]],

            // API Routes
            ['POST', '/api/v1/email/send', [\Modules\EmailMarketing\Controllers\EmailApiController::class, 'send'], ['api_auth']],
            ['POST', '/api/v1/email/send-bulk', [\Modules\EmailMarketing\Controllers\EmailApiController::class, 'sendBulk'], ['api_auth']],
            ['GET', '/api/v1/email/campaigns', [\Modules\EmailMarketing\Controllers\EmailApiController::class, 'campaigns'], ['api_auth']],
            ['GET', '/api/v1/email/campaigns/{id}', [\Modules\EmailMarketing\Controllers\EmailApiController::class, 'getCampaign'], ['api_auth']],
            ['GET', '/api/v1/email/stats', [\Modules\EmailMarketing\Controllers\EmailApiController::class, 'stats'], ['api_auth']],
            ['POST', '/api/v1/workflow/trigger', [\Modules\EmailMarketing\Controllers\EmailApiController::class, 'triggerWorkflow'], ['api_auth']],

            // Webhooks (pour tracking ouvertures/clics)
            ['GET', '/email/track/open/{messageId}', [\Modules\EmailMarketing\Controllers\TrackingController::class, 'trackOpen'], []],
            ['GET', '/email/track/click/{messageId}', [\Modules\EmailMarketing\Controllers\TrackingController::class, 'trackClick'], []],
        ];
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'Email Marketing',
                'icon' => 'mail',
                'children' => [
                    ['title' => 'Dashboard', 'url' => '/admin/email-marketing'],
                    ['title' => 'Campaigns', 'url' => '/admin/email-marketing/campaigns'],
                    ['title' => 'Templates', 'url' => '/admin/email-marketing/templates'],
                    ['title' => 'Workflows', 'url' => '/admin/email-marketing/workflows'],
                    ['title' => 'Analytics', 'url' => '/admin/email-marketing/analytics'],
                ]
            ]
        ];
    }

    /**
     * Services à enregistrer dans le container
     */
    public function getServices(): array
    {
        return [
            'email.gateway.factory' => \Modules\EmailMarketing\Services\EmailGatewayFactory::class,
            'email.sender' => \Modules\EmailMarketing\Services\EmailSenderService::class,
            'email.multichannel' => \Modules\EmailMarketing\Services\MultiChannelService::class,
            'email.analytics' => \Modules\EmailMarketing\Services\CampaignAnalyticsService::class,
        ];
    }
}
