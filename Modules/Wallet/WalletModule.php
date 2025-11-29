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
            ['POST', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'processTopup'], [$authMiddleware]],
            ['POST', '/admin/wallet/debit', [\Modules\Wallet\Controllers\WalletController::class, 'processDebit'], [$authMiddleware]],
            ['GET', '/admin/wallet/transactions/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'transactions'], [$authMiddleware]],
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
