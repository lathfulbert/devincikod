<?php

namespace Modules\Wallet\Controllers;

use App\Core\Application;

class WalletController
{
    public function index()
    {
        $app = Application::getInstance();

        // Mock wallet data
        $wallet = [
            'balance' => 500.00,
            'currency' => 'USD',
            'status' => 'active'
        ];

        $recentTransactions = [
            [
                'id' => 1,
                'type' => 'credit',
                'amount' => 100.00,
                'description' => 'Top-up via PayPal',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'id' => 2,
                'type' => 'debit',
                'amount' => 15.75,
                'description' => 'SMS charges',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ]
        ];

        echo $app->view->render('backend/wallet/index', [
            'wallet' => $wallet,
            'transactions' => $recentTransactions,
            'title' => 'Wallet'
        ]);
    }

    public function history()
    {
        $app = Application::getInstance();

        // Mock full transaction history
        $transactions = [];

        echo $app->view->render('backend/wallet/history', [
            'transactions' => $transactions,
            'title' => 'Transaction History'
        ]);
    }

    public function topup()
    {
        $app = Application::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = $_POST['amount'] ?? 0;
            $method = $_POST['method'] ?? 'paypal';

            // TODO: Process payment

            redirect('/admin/wallet');
            exit;
        }

        echo $app->view->render('backend/wallet/topup', [
            'title' => 'Top-up Wallet'
        ]);
    }
}
