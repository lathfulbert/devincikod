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
            // NOTE: API Routes are now defined in routes/api.php (Laravel Sanctum style)
            // This provides better separation and automatic /api prefix + middleware

            // Dashboard
            ['GET', '/admin/sms', [\Modules\SmsCore\Controllers\DashboardController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/sms/statistics', [\Modules\SmsCore\Controllers\DashboardController::class, 'statistics'], [$authMiddleware]],

            // SMS Management
            ['GET', '/admin/sms/send', [\Modules\SmsCore\Controllers\SmsController::class, 'send'], [$authMiddleware]],
            ['POST', '/admin/sms/send', [\Modules\SmsCore\Controllers\SmsController::class, 'send'], [$authMiddleware]],
            ['POST', '/admin/sms/send-bulk', [\Modules\SmsCore\Controllers\SmsController::class, 'sendBulk'], [$authMiddleware]],
            ['GET', '/admin/sms/download-template', [\Modules\SmsCore\Controllers\SmsController::class, 'downloadTemplate'], [$authMiddleware]],
            ['GET', '/admin/sms/contracts', [\Modules\SmsCore\Controllers\SmsController::class, 'contracts'], [$authMiddleware]],
            ['GET', '/admin/sms/history', [\Modules\SmsCore\Controllers\SmsController::class, 'history'], [$authMiddleware]],
            ['GET', '/admin/sms/details/{id}', [\Modules\SmsCore\Controllers\SmsController::class, 'details'], [$authMiddleware]],
            ['GET', '/admin/sms/bulk', [\Modules\SmsCore\Controllers\SmsController::class, 'bulk'], [$authMiddleware]],
            ['POST', '/admin/sms/bulk', [\Modules\SmsCore\Controllers\SmsController::class, 'bulk'], [$authMiddleware]],

            // Provider Dashboard
            ['GET', '/admin/sms/providers', [\Modules\SmsCore\Controllers\ProviderDashboardController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/sms/providers/orange', [\Modules\SmsCore\Controllers\ProviderDashboardController::class, 'orange'], [$authMiddleware]],

            // Campaigns
            ['GET', '/admin/sms/campaigns', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/sms/campaigns/create', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'create'], [$authMiddleware]],
            ['POST', '/admin/sms/campaigns/store', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'store'], [$authMiddleware]],
            ['GET', '/admin/sms/campaigns/{id}', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'show'], [$authMiddleware]],
            ['GET', '/admin/sms/campaigns/{id}/edit', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'edit'], [$authMiddleware]],
            ['POST', '/admin/sms/campaigns/{id}/update', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/sms/campaigns/preview', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'preview'], [$authMiddleware]],
            ['POST', '/admin/sms/campaigns/{id}/delete', [\Modules\SmsCore\Controllers\SmsCampaignController::class, 'delete'], [$authMiddleware]],

            // Billing & Pricing
            ['GET', '/admin/sms/pricing', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/update', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'update'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/update-default', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'updateDefault'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/update-country', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'updateCountry'], [$authMiddleware]],
            ['POST', '/admin/sms/pricing/delete-country', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'deleteCountry'], [$authMiddleware]],
            ['GET', '/admin/sms/billing', [\Modules\SmsCore\Controllers\SmsPricingController::class, 'logs'], [$authMiddleware]],

            // NOTE: Wallet routes are now handled by the Wallet Module (Modules\Wallet\WalletModule)

            // Sender Names Management
            ['GET', '/sms/sender-names', [\Modules\SmsCore\Controllers\SenderNameController::class, 'index'], [$authMiddleware]],
            ['GET', '/sms/sender-names/create', [\Modules\SmsCore\Controllers\SenderNameController::class, 'create'], [$authMiddleware]],
            ['POST', '/sms/sender-names/store', [\Modules\SmsCore\Controllers\SenderNameController::class, 'store'], [$authMiddleware]],
            ['GET', '/sms/sender-names/edit', [\Modules\SmsCore\Controllers\SenderNameController::class, 'edit'], [$authMiddleware]],
            ['POST', '/sms/sender-names/update', [\Modules\SmsCore\Controllers\SenderNameController::class, 'update'], [$authMiddleware]],
            ['POST', '/sms/sender-names/delete', [\Modules\SmsCore\Controllers\SenderNameController::class, 'delete'], [$authMiddleware]],
            ['GET', '/sms/sender-names/assign-users', [\Modules\SmsCore\Controllers\SenderNameController::class, 'assignUsers'], [$authMiddleware]],
            ['POST', '/sms/sender-names/save-assignments', [\Modules\SmsCore\Controllers\SenderNameController::class, 'saveAssignments'], [$authMiddleware]],
            ['POST', '/sms/sender-names/bulk-assign-to-user', [\Modules\SmsCore\Controllers\SenderNameController::class, 'bulkAssignToUser'], [$authMiddleware]],

            // API Documentation
            ['GET', '/admin/sms/api/docs', [\Modules\SmsCore\Controllers\ApiDocsController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/sms/api/keys', [\Modules\SmsCore\Controllers\ApiDocsController::class, 'keys'], [$authMiddleware]],
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
                    ['title' => 'Sender Names', 'url' => '/sms/sender-names'],
                    ['title' => 'Tarification', 'url' => '/admin/sms/pricing'],
                    ['title' => 'Facturation', 'url' => '/admin/sms/billing'],
                    ['title' => 'Fournisseurs (Stats)', 'url' => '/admin/sms/providers', 'icon' => 'server'],
                    ['title' => '---', 'url' => '#'], // Separator
                    ['title' => 'Documentation API', 'url' => '/admin/sms/api/docs', 'icon' => 'book'],
                    ['title' => 'Mes clés API', 'url' => '/admin/api-keys', 'icon' => 'key'],
                ]
            ]
        ];
    }
}
