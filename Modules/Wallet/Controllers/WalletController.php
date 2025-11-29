<?php

namespace Modules\Wallet\Controllers;

use App\Core\Application;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Models\Wallet;

class WalletController
{
    protected WalletService $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    /**
     * List all user wallets (Admin)
     */
    public function index()
    {
        $app = Application::getInstance();
        $wallets = $this->walletService->getAllWallets();

        echo $app->view->render('backend/wallet/index', [
            'wallets' => $wallets,
            'title' => 'Wallet Management'
        ]);
    }

    /**
     * Show top-up form for a user
     */
    public function topup($userId = null)
    {
        $app = Application::getInstance();
        $userId = $userId ?? ($_GET['user_id'] ?? null);

        if (!$userId) {
            $_SESSION['flash_error'] = 'User ID is required';
            redirect('/admin/wallet');
            exit;
        }

        $wallet = $this->walletService->getWallet($userId);

        echo $app->view->render('backend/wallet/topup', [
            'wallet' => $wallet,
            'userId' => $userId,
            'title' => 'Top-up Wallet'
        ]);
    }

    /**
     * Process top-up
     */
    public function processTopup()
    {
        $userId = $_POST['user_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $description = $_POST['description'] ?? 'Admin credit';

        if (!$userId || $amount <= 0) {
            $_SESSION['flash_error'] = 'Invalid user ID or amount';
            redirect('/admin/wallet');
            exit;
        }

        try {
            $this->walletService->addCredit($userId, $amount, $description);
            $_SESSION['flash_success'] = "Successfully added {$amount} XOF to wallet";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to add credit: ' . $e->getMessage();
        }

        redirect('/admin/wallet');
        exit;
    }

    /**
     * Process debit
     */
    public function processDebit()
    {
        $userId = $_POST['user_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $description = $_POST['description'] ?? 'Admin debit';

        if (!$userId || $amount <= 0) {
            $_SESSION['flash_error'] = 'Invalid user ID or amount';
            redirect('/admin/wallet');
            exit;
        }

        try {
            $this->walletService->deductCredit($userId, $amount, $description);
            $_SESSION['flash_success'] = "Successfully deducted {$amount} XOF from wallet";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to deduct credit: ' . $e->getMessage();
        }

        redirect('/admin/wallet');
        exit;
    }

    /**
     * View wallet transactions
     */
    public function transactions($userId)
    {
        $app = Application::getInstance();
        $transactions = $this->walletService->getTransactions($userId, 50);
        $wallet = $this->walletService->getWallet($userId);

        echo $app->view->render('backend/wallet/transactions', [
            'transactions' => $transactions,
            'wallet' => $wallet,
            'title' => 'Wallet Transactions'
        ]);
    }
}
