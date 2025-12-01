<?php

namespace Modules\SmsCore;

use App\Core\Module\AbstractModule;

class SmsCoreModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // API Routes (no middleware for now, will be added in controller)
            ['POST', '/api/v1/sms/send', [\Modules\SmsCore\Controllers\SmsApiController::class, 'send'], []],
            ['GET', '/api/v1/sms/history', [\Modules\SmsCore\Controllers\SmsApiController::class, 'history'], []],
            ['GET', '/api/v1/sms/balance', [\Modules\SmsCore\Controllers\SmsApiController::class, 'balance'], []],

            // Dashboard
            ['GET', '/admin/sms', [\Modules\SmsCore\Controllers\DashboardController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/sms/statistics', [\Modules\SmsCore\Controllers\DashboardController::class, 'statistics'], [$authMiddleware]],

            // SMS Management
            ['GET', '/admin/sms/send', [\Modules\SmsCore\Controllers\SmsController::class, 'send'], [$authMiddleware]],
            ['POST', '/admin/sms/send', [\Modules\SmsCore\Controllers\SmsController::class, 'send'], [$authMiddleware]],
            ['GET', '/admin/sms/history', [\Modules\SmsCore\Controllers\SmsController::class, 'history'], [$authMiddleware]],
            ['GET', '/admin/sms/details/{id}', [\Modules\SmsCore\Controllers\SmsController::class, 'details'], [$authMiddleware]],
            ['GET', '/admin/sms/bulk', [\Modules\SmsCore\Controllers\SmsController::class, 'bulk'], [$authMiddleware]],
            ['POST', '/admin/sms/bulk', [\Modules\SmsCore\Controllers\SmsController::class, 'bulk'], [$authMiddleware]],

            // Campaigns
            ['GET', '/admin/sms/campaigns', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/sms/campaigns/create', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/sms/campaigns/store', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/sms/campaigns/{id}', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'show'], [$authMiddleware]],
            ['GET', '/admin/sms/campaigns/{id}/edit', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/sms/campaigns/{id}/update', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/sms/campaigns/preview', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'preview'], [$authMiddleware]],
            ['GET', '/admin/sms/campaigns/{id}/delete', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'delete'], [$authMiddleware]],

            // Billing & Pricing
            ['GET', '/admin/sms/pricing', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/update', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/update-default', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'updateDefault'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/update-country', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'updateCountry'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/delete-country', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'deleteCountry'], [$authMiddleware]],
            ['GET', '/admin/sms/billing', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'logs'], [$authMiddleware]],
        ];
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'SMS',
                'icon' => 'message-circle',
                'children' => [
                    ['title' => 'Dashboard', 'url' => '/admin/sms'],
                    ['title' => 'Send SMS', 'url' => '/admin/sms/send'],
                    ['title' => 'Bulk SMS', 'url' => '/admin/sms/bulk'],
                    ['title' => 'Campaigns', 'url' => '/admin/sms/campaigns'],
                    ['title' => 'History', 'url' => '/admin/sms/history'],
                    ['title' => 'Statistics', 'url' => '/admin/sms/statistics'],
                    ['title' => 'Tarification', 'url' => '/admin/sms/pricing'],
                    ['title' => 'Facturation', 'url' => '/admin/sms/billing'],
                    ['title' => 'API Keys', 'url' => '/admin/api-keys', 'icon' => 'key'],
                ]
            ]
        ];
    }
}
