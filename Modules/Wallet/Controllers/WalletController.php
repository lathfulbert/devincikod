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

        echo view('Wallet/wallet/index', [
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

        // Si pas d'userId fourni, utiliser l'utilisateur connecté
        if (!$userId) {
            $userId = $_GET['user_id'] ?? $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
        }

        if (!$userId) {
            $_SESSION['flash_error'] = 'User ID is required';
            redirect('/admin/dashboard');
            exit;
        }

        $wallet = $this->walletService->getWallet($userId);

        echo view('Wallet/wallet/topup', [
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
        // Si pas d'userId dans POST, utiliser l'utilisateur connecté
        $userId = $_POST['user_id'] ?? $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $description = $_POST['description'] ?? 'Top-up via payment gateway';
        $method = $_POST['method'] ?? 'paypal';

        if (!$userId || $amount <= 0) {
            $_SESSION['flash_error'] = 'Invalid user ID or amount';
            redirect('/admin/wallet/topup');
            exit;
        }

        try {
            $this->walletService->addCredit($userId, $amount, $description . ' (' . $method . ')');
            $_SESSION['flash_success'] = "Successfully added {$amount} XOF to your wallet";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to add credit: ' . $e->getMessage();
        }

        redirect('/admin/sms');
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

        echo view('Wallet/wallet/transactions', [
            'transactions' => $transactions,
            'wallet' => $wallet,
            'title' => 'Wallet Transactions'
        ]);
    }
}
