<?php

use Modules\SmsCore\Controllers\DashboardController;
use Modules\SmsCore\Controllers\SmsController;
use Modules\SmsCore\Controllers\SmsCampaignController;
use Modules\SmsCore\Controllers\SmsPricingController;
use Modules\SmsCore\Controllers\ProviderDashboardController;
use Modules\SmsCore\Controllers\ApiDocsController;

/** @var \App\Core\Routing\Router $router */

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

// Dashboard
$router->get('/admin/sms', [DashboardController::class, 'index'], $authMiddleware);
$router->get('/admin/sms/statistics', [DashboardController::class, 'statistics'], $authMiddleware);

// SMS Management
$router->get('/admin/sms/send', [SmsController::class, 'send'], $authMiddleware);
$router->post('/admin/sms/send', [SmsController::class, 'send'], $authMiddleware);
$router->post('/admin/sms/send-bulk', [SmsController::class, 'sendBulk'], $authMiddleware);
$router->post('/admin/sms/parse-file', [SmsController::class, 'parseFile'], $authMiddleware);
$router->get('/admin/sms/download-template', [SmsController::class, 'downloadTemplate'], $authMiddleware);
$router->get('/admin/sms/contracts', [SmsController::class, 'contracts'], $authMiddleware);
$router->get('/admin/sms/history', [SmsController::class, 'history'], $authMiddleware);
$router->get('/admin/sms/details/{id}', [SmsController::class, 'details'], $authMiddleware);
$router->get('/admin/sms/bulk', [SmsController::class, 'bulk'], $authMiddleware);
$router->post('/admin/sms/bulk', [SmsController::class, 'bulk'], $authMiddleware);

// Provider Dashboard
$router->get('/admin/sms/providers', [ProviderDashboardController::class, 'index'], $authMiddleware);
$router->get('/admin/sms/providers/orange', [ProviderDashboardController::class, 'orange'], $authMiddleware);

// Campaigns
$router->get('/admin/sms/campaigns', [SmsCampaignController::class, 'index'], $authMiddleware);
$router->get('/admin/sms/campaigns/create', [SmsCampaignController::class, 'create'], $authMiddleware);
$router->post('/admin/sms/campaigns/store', [SmsCampaignController::class, 'store'], $authMiddleware);
$router->get('/admin/sms/campaigns/{id}', [SmsCampaignController::class, 'show'], $authMiddleware);
$router->get('/admin/sms/campaigns/{id}/edit', [SmsCampaignController::class, 'edit'], $authMiddleware);
$router->post('/admin/sms/campaigns/{id}/update', [SmsCampaignController::class, 'update'], $authMiddleware);
$router->post('/admin/sms/campaigns/preview', [SmsCampaignController::class, 'preview'], $authMiddleware);
$router->post('/admin/sms/campaigns/{id}/delete', [SmsCampaignController::class, 'delete'], $authMiddleware);

// Billing & Pricing
$router->get('/admin/sms/pricing', [SmsPricingController::class, 'index'], $authMiddleware);
$router->post('/admin/sms/pricing/update', [SmsPricingController::class, 'update'], $authMiddleware);
$router->post('/admin/sms/pricing/update-default', [SmsPricingController::class, 'updateDefault'], $authMiddleware);
$router->post('/admin/sms/pricing/update-country', [SmsPricingController::class, 'updateCountry'], $authMiddleware);
$router->post('/admin/sms/pricing/delete-country', [SmsPricingController::class, 'deleteCountry'], $authMiddleware);
$router->get('/admin/sms/billing', [SmsPricingController::class, 'logs'], $authMiddleware);

// API Documentation
$router->get('/admin/sms/api/docs', [ApiDocsController::class, 'index'], $authMiddleware);
$router->get('/admin/sms/api/keys', [ApiDocsController::class, 'keys'], $authMiddleware);
