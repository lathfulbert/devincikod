<?php

namespace Modules\Wallet\Controllers;

use App\Core\Application;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Services\WalletNotificationService;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletTopupRequest;

class WalletController
{
    protected WalletService $walletService;
    protected WalletNotificationService $notificationService;

    public function __construct()
    {
        $this->walletService = new WalletService();
        $this->notificationService = new WalletNotificationService();
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
     * Process top-up request
     */
    public function processTopup()
    {
        // Si pas d'userId dans POST, utiliser l'utilisateur connecté
        $userId = $_POST['user_id'] ?? $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'gateway';
        $gatewayId = $_POST['gateway_id'] ?? null;
        $notes = $_POST['notes'] ?? '';

        // Validations
        if (!$userId) {
            $_SESSION['flash_error'] = 'ID utilisateur invalide';
            redirect('/admin/wallet/topup');
            exit;
        }

        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Le montant doit être supérieur à 0';
            redirect('/admin/wallet/topup');
            exit;
        }

        if ($amount < 100) {
            $_SESSION['flash_error'] = 'Le montant minimum est de 100 XOF';
            redirect('/admin/wallet/topup');
            exit;
        }

        if ($amount > 10000000) {
            $_SESSION['flash_error'] = 'Le montant maximum par recharge est de 10,000,000 XOF';
            redirect('/admin/wallet/topup');
            exit;
        }

        // Vérifier que le solde résultant ne dépassera pas la limite
        $currentBalance = $this->walletService->getBalance($userId);
        $newBalance = $currentBalance + $amount;

        if ($newBalance > 9999999999999.99) {
            $_SESSION['flash_error'] = 'Cette recharge dépasserait la limite maximale du solde';
            redirect('/admin/wallet/topup');
            exit;
        }

        try {
            $wallet = $this->walletService->getWallet($userId);

            // Créer la demande de recharge
            $request = WalletTopupRequest::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'currency' => 'XOF',
                'payment_method' => $paymentMethod,
                'gateway_id' => $gatewayId,
                'status' => 'pending',
                'notes' => $notes,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            // Si paiement par gateway
            if ($paymentMethod === 'gateway' && $gatewayId) {
                // TODO: Rediriger vers la passerelle de paiement
                // Pour l'instant, on simule un paiement réussi
                $request->update([
                    'gateway_status' => 'SUCCESS',
                    'gateway_transaction_id' => 'SIMULATED_' . time(),
                    'status' => 'completed'
                ]);

                // Créditer directement le wallet
                $this->walletService->addCredit($userId, $amount, 'Recharge via gateway #' . $request->id);
                $request->markAsCompleted();

                // Envoyer notification d'approbation immédiate
                $newBalance = $this->walletService->getBalance($userId);
                $this->notificationService->sendRequestApprovedNotification($request, $newBalance);

                $_SESSION['flash_success'] = "Recharge de " . number_format($amount, 0, ',', ' ') . " XOF effectuée avec succès";
            } else {
                // Paiement offline - en attente de validation admin

                // Envoyer notification de confirmation à l'utilisateur
                $this->notificationService->sendRequestSubmittedNotification($request);

                // Envoyer notification aux administrateurs
                $this->notificationService->sendAdminNotification($request);

                $_SESSION['flash_info'] = "Votre demande de recharge de " . number_format($amount, 0, ',', ' ') . " XOF a été enregistrée et est en attente de validation par un administrateur.";
            }

            redirect('/admin/wallet/requests');
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Échec de la demande : ' . $e->getMessage();
            redirect('/admin/wallet/topup');
            exit;
        }
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

    /**
     * View top-up requests for current user
     */
    public function requests()
    {
        $app = Application::getInstance();
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['flash_error'] = 'Utilisateur non authentifié';
            redirect('/login');
            exit;
        }

        $requests = WalletTopupRequest::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $wallet = $this->walletService->getWallet($userId);

        echo view('Wallet/wallet/requests', [
            'requests' => $requests,
            'wallet' => $wallet,
            'title' => 'Mes demandes de recharge'
        ]);
    }

    /**
     * View all top-up requests (Admin)
     */
    public function adminRequests()
    {
        $app = Application::getInstance();

        // Filter by status
        $status = $_GET['status'] ?? null;
        $query = WalletTopupRequest::query()->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->get();

        // Get pending count
        $pendingCount = WalletTopupRequest::where('status', 'pending')->count();

        echo view('Wallet/wallet/admin-requests', [
            'requests' => $requests,
            'pendingCount' => $pendingCount,
            'currentStatus' => $status,
            'title' => 'Demandes de recharge'
        ]);
    }

    /**
     * Approve a top-up request (Admin)
     */
    public function approveRequest($id)
    {
        $request = WalletTopupRequest::find($id);

        if (!$request) {
            $_SESSION['flash_error'] = 'Demande introuvable';
            redirect('/admin/wallet/admin-requests');
            exit;
        }

        if (!$request->isPending()) {
            $_SESSION['flash_error'] = 'Cette demande a déjà été traitée';
            redirect('/admin/wallet/admin-requests');
            exit;
        }

        $adminId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
        $adminNotes = $_POST['admin_notes'] ?? 'Demande approuvée';

        try {
            // Marquer comme approuvée
            $request->markAsApproved($adminId, $adminNotes);

            // Créditer le wallet
            $this->walletService->addCredit(
                $request->user_id,
                $request->amount,
                'Recharge manuelle approuvée (Demande #' . $request->id . ')'
            );

            // Marquer comme complétée
            $request->markAsCompleted();

            // Envoyer notification d'approbation à l'utilisateur
            $newBalance = $this->walletService->getBalance($request->user_id);
            $this->notificationService->sendRequestApprovedNotification($request, $newBalance);

            $_SESSION['flash_success'] = 'Demande approuvée et crédit ajouté au wallet';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erreur lors de l\'approbation : ' . $e->getMessage();
        }

        redirect('/admin/wallet/admin-requests');
        exit;
    }

    /**
     * Reject a top-up request (Admin)
     */
    public function rejectRequest($id)
    {
        $request = WalletTopupRequest::find($id);

        if (!$request) {
            $_SESSION['flash_error'] = 'Demande introuvable';
            redirect('/admin/wallet/admin-requests');
            exit;
        }

        if (!$request->isPending()) {
            $_SESSION['flash_error'] = 'Cette demande a déjà été traitée';
            redirect('/admin/wallet/admin-requests');
            exit;
        }

        $adminId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
        $adminNotes = $_POST['admin_notes'] ?? 'Demande rejetée';

        $request->markAsRejected($adminId, $adminNotes);

        // Envoyer notification de rejet à l'utilisateur
        $this->notificationService->sendRequestRejectedNotification($request);

        $_SESSION['flash_success'] = 'Demande rejetée';
        redirect('/admin/wallet/admin-requests');
        exit;
    }
}
