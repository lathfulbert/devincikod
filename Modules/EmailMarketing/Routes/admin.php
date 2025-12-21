<?php

use Modules\EmailMarketing\Controllers\DashboardController;
use Modules\EmailMarketing\Controllers\EmailCampaignController;
use Modules\EmailMarketing\Controllers\EmailTemplateController;
use Modules\EmailMarketing\Controllers\WorkflowController;
use Modules\EmailMarketing\Controllers\AnalyticsController;
use Modules\EmailMarketing\Controllers\EmailApiController;
use Modules\EmailMarketing\Controllers\TrackingController;

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

$router->get('/admin/email-marketing', [DashboardController::class, 'index'], $authMiddleware)
    ->name('admin.email-marketing.index');
$router->get('/admin/email-marketing/statistics', [DashboardController::class, 'statistics'], $authMiddleware)
    ->name('admin.email-marketing.statistics');

// Campaigns
$router->get('/admin/email-marketing/campaigns', [EmailCampaignController::class, 'index'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.index');
$router->get('/admin/email-marketing/campaigns/create', [EmailCampaignController::class, 'create'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.create');
$router->post('/admin/email-marketing/campaigns/store', [EmailCampaignController::class, 'store'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.store');
$router->get('/admin/email-marketing/campaigns/{id}', [EmailCampaignController::class, 'show'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.show');
$router->get('/admin/email-marketing/campaigns/{id}/edit', [EmailCampaignController::class, 'edit'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.edit');
$router->post('/admin/email-marketing/campaigns/{id}/update', [EmailCampaignController::class, 'update'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.update');
$router->post('/admin/email-marketing/campaigns/{id}/send', [EmailCampaignController::class, 'send'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.send');
$router->post('/admin/email-marketing/campaigns/{id}/pause', [EmailCampaignController::class, 'pause'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.pause');
$router->post('/admin/email-marketing/campaigns/{id}/resume', [EmailCampaignController::class, 'resume'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.resume');
$router->get('/admin/email-marketing/campaigns/{id}/delete', [EmailCampaignController::class, 'delete'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.delete');
$router->get('/admin/email-marketing/campaigns/{id}/analytics', [EmailCampaignController::class, 'analytics'], $authMiddleware)
    ->name('admin.email-marketing.campaigns.analytics');

// Templates
$router->get('/admin/email-marketing/templates', [EmailTemplateController::class, 'index'], $authMiddleware)
    ->name('admin.email-marketing.templates.index');
$router->get('/admin/email-marketing/templates/create', [EmailTemplateController::class, 'create'], $authMiddleware)
    ->name('admin.email-marketing.templates.create');
$router->post('/admin/email-marketing/templates/store', [EmailTemplateController::class, 'store'], $authMiddleware)
    ->name('admin.email-marketing.templates.store');
$router->get('/admin/email-marketing/templates/{id}', [EmailTemplateController::class, 'show'], $authMiddleware)
    ->name('admin.email-marketing.templates.show');
$router->get('/admin/email-marketing/templates/{id}/edit', [EmailTemplateController::class, 'edit'], $authMiddleware)
    ->name('admin.email-marketing.templates.edit');
$router->post('/admin/email-marketing/templates/{id}/update', [EmailTemplateController::class, 'update'], $authMiddleware)
    ->name('admin.email-marketing.templates.update');
$router->post('/admin/email-marketing/templates/{id}/duplicate', [EmailTemplateController::class, 'duplicate'], $authMiddleware)
    ->name('admin.email-marketing.templates.duplicate');
$router->get('/admin/email-marketing/templates/{id}/delete', [EmailTemplateController::class, 'delete'], $authMiddleware)
    ->name('admin.email-marketing.templates.delete');
$router->post('/admin/email-marketing/templates/{id}/test', [EmailTemplateController::class, 'sendTest'], $authMiddleware)
    ->name('admin.email-marketing.templates.test');
$router->get('/admin/email-marketing/templates/{id}/preview', [EmailTemplateController::class, 'preview'], $authMiddleware)
    ->name('admin.email-marketing.templates.preview');

// Workflows
$router->get('/admin/email-marketing/workflows', [WorkflowController::class, 'index'], $authMiddleware)
    ->name('admin.email-marketing.workflows.index');
$router->get('/admin/email-marketing/workflows/create', [WorkflowController::class, 'create'], $authMiddleware)
    ->name('admin.email-marketing.workflows.create');
$router->post('/admin/email-marketing/workflows/store', [WorkflowController::class, 'store'], $authMiddleware)
    ->name('admin.email-marketing.workflows.store');
$router->get('/admin/email-marketing/workflows/{id}', [WorkflowController::class, 'show'], $authMiddleware)
    ->name('admin.email-marketing.workflows.show');
$router->get('/admin/email-marketing/workflows/{id}/edit', [WorkflowController::class, 'edit'], $authMiddleware)
    ->name('admin.email-marketing.workflows.edit');
$router->post('/admin/email-marketing/workflows/{id}/update', [WorkflowController::class, 'update'], $authMiddleware)
    ->name('admin.email-marketing.workflows.update');
$router->post('/admin/email-marketing/workflows/{id}/activate', [WorkflowController::class, 'activate'], $authMiddleware)
    ->name('admin.email-marketing.workflows.activate');
$router->post('/admin/email-marketing/workflows/{id}/pause', [WorkflowController::class, 'pause'], $authMiddleware)
    ->name('admin.email-marketing.workflows.pause');
$router->post('/admin/email-marketing/workflows/{id}/execute', [WorkflowController::class, 'execute'], $authMiddleware)
    ->name('admin.email-marketing.workflows.execute');
$router->get('/admin/email-marketing/workflows/{id}/delete', [WorkflowController::class, 'delete'], $authMiddleware)
    ->name('admin.email-marketing.workflows.delete');

// Analytics
$router->get('/admin/email-marketing/analytics', [AnalyticsController::class, 'index'], $authMiddleware)
    ->name('admin.email-marketing.analytics');
$router->get('/admin/email-marketing/analytics/campaign/{id}', [AnalyticsController::class, 'campaign'], $authMiddleware)
    ->name('admin.email-marketing.analytics.campaign');
$router->get('/admin/email-marketing/analytics/compare', [AnalyticsController::class, 'compare'], $authMiddleware)
    ->name('admin.email-marketing.analytics.compare');
$router->get('/admin/email-marketing/analytics/multichannel/{id}', [AnalyticsController::class, 'multichannel'], $authMiddleware)
    ->name('admin.email-marketing.analytics.multichannel');

// API & Webhooks peuvent être ajoutés ici si besoin
