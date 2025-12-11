<?php

namespace Modules\Wallet;

use App\Core\Module\AbstractModule;

class WalletModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // Wallet Management
            ['GET', '/admin/wallet', [\Modules\Wallet\Controllers\WalletController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'topup'], [$authMiddleware]],
            ['POST', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'processTopup'], [$authMiddleware]],
            ['POST', '/admin/wallet/debit', [\Modules\Wallet\Controllers\WalletController::class, 'processDebit'], [$authMiddleware]],
            ['GET', '/admin/wallet/transactions/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'transactions'], [$authMiddleware]],

            // Topup Requests
            ['GET', '/admin/wallet/requests', [\Modules\Wallet\Controllers\WalletController::class, 'requests'], [$authMiddleware]],
            ['GET', '/admin/wallet/admin-requests', [\Modules\Wallet\Controllers\WalletController::class, 'adminRequests'], [$authMiddleware]],
            ['POST', '/admin/wallet/approve/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'approveRequest'], [$authMiddleware]],
            ['POST', '/admin/wallet/reject/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'rejectRequest'], [$authMiddleware]],

            // Payment Gateway Callbacks (no auth middleware - called by external gateways)
            ['GET', '/admin/wallet/payment-return', [\Modules\Wallet\Controllers\WalletController::class, 'paymentReturn'], [$authMiddleware]],
            ['GET', '/admin/wallet/payment-cancel', [\Modules\Wallet\Controllers\WalletController::class, 'paymentCancel'], [$authMiddleware]],
            ['POST', '/api/webhook/payment/{gatewayCode}', [\Modules\Wallet\Controllers\WalletController::class, 'handlePaymentCallback'], []],
        ];
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'link',
                'title' => 'Wallet',
                'icon' => 'dollar-sign',
                'url' => '/admin/wallet',
                'class' => 'link-nav'
            ]
        ];
    }
}
