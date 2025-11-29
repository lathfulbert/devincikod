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

            // Billing & Pricing
            ['GET', '/admin/sms/pricing', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/update', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'update'], [$authMiddleware]],
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
                    ['title' => 'History', 'url' => '/admin/sms/history'],
                    ['title' => 'Statistics', 'url' => '/admin/sms/statistics'],
                    ['title' => 'Tarification', 'url' => '/admin/sms/pricing'],
                    ['title' => 'Facturation', 'url' => '/admin/sms/billing'],
                ]
            ]
        ];
    }
}
