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
            ['GET', '/admin/wallet/history', [\Modules\Wallet\Controllers\WalletController::class, 'history'], [$authMiddleware]],
            ['GET', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'topup'], [$authMiddleware]],
            ['POST', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'topup'], [$authMiddleware]],
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
