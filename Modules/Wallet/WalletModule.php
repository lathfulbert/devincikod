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
            // Wallet Dashboard (avec statistiques)
            ['GET', '/admin/wallet', [\Modules\Wallet\Controllers\WalletController::class, 'dashboard'], [$authMiddleware]],

            // Gestion des Wallets (liste/admin)
            ['GET', '/admin/wallet/manage', [\Modules\Wallet\Controllers\WalletController::class, 'manage'], [$authMiddleware]],

            // Recharger mon wallet
            ['GET', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'topup'], [$authMiddleware]],
            ['POST', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'processTopup'], [$authMiddleware]],

            // Mes demandes de recharge
            ['GET', '/admin/wallet/requests', [\Modules\Wallet\Controllers\WalletController::class, 'myRequests'], [$authMiddleware]],
            ['POST', '/admin/wallet/cancel-request', [\Modules\Wallet\Controllers\WalletController::class, 'cancelRequest'], [$authMiddleware]],

            // Gestion des demandes (Admin)
            ['GET', '/admin/wallet/admin-requests', [\Modules\Wallet\Controllers\WalletController::class, 'adminRequests'], [$authMiddleware]],
            ['POST', '/admin/wallet/approve/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'approveRequest'], [$authMiddleware]],
            ['POST', '/admin/wallet/reject/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'rejectRequest'], [$authMiddleware]],

            // Autres fonctionnalités existantes
            ['POST', '/admin/wallet/debit', [\Modules\Wallet\Controllers\WalletController::class, 'processDebit'], [$authMiddleware]],
            ['GET', '/admin/wallet/transactions/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'transactions'], [$authMiddleware]],

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
                'type' => 'dropdown',
                'title' => 'Wallet',
                'icon' => 'dollar-sign',
                'permission' => 'access.wallet',
                'children' => [
                    [
                        'title' => 'Dashboard',
                        'url' => '/admin/wallet',
                        'permission' => 'wallet.dashboard'
                    ],
                    [
                        'title' => 'Gérer les Wallets',
                        'url' => '/admin/wallet/manage',
                        'permission' => 'wallet.manage'
                    ],
                    [
                        'title' => 'Recharger mon Wallet',
                        'url' => '/admin/wallet/topup',
                        'permission' => 'wallet.topup'
                    ],
                    [
                        'title' => 'Mes demandes de recharge',
                        'url' => '/admin/wallet/requests',
                        'permission' => 'wallet.requests.view'
                    ],
                    [
                        'title' => 'Gestion des demandes (Admin)',
                        'url' => '/admin/wallet/admin-requests',
                        'permission' => 'wallet.requests.manage'
                    ]
                ]
            ]
        ];
    }
}
