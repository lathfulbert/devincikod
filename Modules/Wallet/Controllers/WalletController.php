<?php

namespace Modules\Wallet\Controllers;

use App\Core\Application;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Services\WalletNotificationService;
use Modules\Wallet\Services\PaymentGatewayManager;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletTopupRequest;
use Modules\Wallet\Models\SmsGateway;

class WalletController
{
    protected WalletService $walletService;
    protected WalletNotificationService $notificationService;
    protected PaymentGatewayManager $gatewayManager;

    public function __construct()
    {
        $this->walletService = new WalletService();
        $this->notificationService = new WalletNotificationService();
        $this->gatewayManager = new PaymentGatewayManager();
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

        // Get available payment gateways
        $activeGateways = $this->gatewayManager->getActiveGateways();

        echo view('Wallet/wallet/topup', [
            'wallet' => $wallet,
            'userId' => $userId,
            'gateways' => $activeGateways,
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
        $gatewayCode = $_POST['gateway_code'] ?? null; // Code du gateway (cinetpay, wave, etc.)
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
                'gateway_id' => null, // Will be set after gateway response
                'status' => 'pending',
                'notes' => $notes,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            // Si paiement par gateway
            if ($paymentMethod === 'gateway' && $gatewayCode) {
                try {
                    // Get user information
                    $user = \Modules\Auth\Models\User::find($userId);

                    // Generate unique reference
                    $reference = 'TOPUP_' . $request->id . '_' . time();

                    // Build return and webhook URLs
                    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
                    $returnUrl = $baseUrl . '/admin/wallet/payment-return?request_id=' . $request->id;
                    $cancelUrl = $baseUrl . '/admin/wallet/payment-cancel?request_id=' . $request->id;
                    $webhookUrl = $baseUrl . '/api/webhook/payment/' . $gatewayCode;

                    // Prepare payment data
                    $paymentData = [
                        'amount' => $amount,
                        'currency' => 'XOF',
                        'reference' => $reference,
                        'description' => 'Recharge Wallet - ' . number_format($amount, 0, ',', ' ') . ' XOF',
                        'return_url' => $returnUrl,
                        'cancel_url' => $cancelUrl,
                        'webhook_url' => $webhookUrl,
                        'customer_name' => $user->name ?? 'Client',
                        'customer_email' => $user->email ?? '',
                        'customer_phone' => $user->phone ?? '',
                        'metadata' => [
                            'request_id' => $request->id,
                            'user_id' => $userId,
                            'wallet_id' => $wallet->id
                        ]
                    ];

                    // Initiate payment through gateway
                    $response = $this->gatewayManager->initiatePayment($gatewayCode, $paymentData);

                    if ($response['success']) {
                        // Update request with gateway details
                        $request->update([
                            'gateway_transaction_id' => $reference,
                            'gateway_response' => json_encode($response),
                            'status' => 'processing'
                        ]);

                        // Store payment URL in session for redirect
                        $_SESSION['payment_url'] = $response['payment_url'];
                        $_SESSION['flash_info'] = 'Redirection vers la passerelle de paiement...';

                        // Redirect to payment gateway
                        redirect($response['payment_url']);
                        exit;
                    } else {
                        // Payment initiation failed
                        $request->update([
                            'gateway_response' => json_encode($response),
                            'gateway_status' => 'FAILED',
                            'status' => 'failed'
                        ]);

                        $_SESSION['flash_error'] = 'Échec de l\'initialisation du paiement: ' . ($response['error'] ?? 'Erreur inconnue');
                        redirect('/admin/wallet/topup');
                        exit;
                    }

                } catch (\Exception $e) {
                    $request->update([
                        'gateway_status' => 'ERROR',
                        'status' => 'failed',
                        'gateway_response' => json_encode(['error' => $e->getMessage()])
                    ]);

                    $_SESSION['flash_error'] = 'Erreur lors de l\'initialisation du paiement: ' . $e->getMessage();
                    redirect('/admin/wallet/topup');
                    exit;
                }
            } else {
                // Paiement offline - en attente de validation admin

                // Envoyer notification de confirmation à l'utilisateur
                $this->notificationService->sendRequestSubmittedNotification($request);

                // Envoyer notification aux administrateurs
                $this->notificationService->sendAdminNotification($request);

                $_SESSION['flash_info'] = "Votre demande de recharge de " . number_format($amount, 0, ',', ' ') . " XOF a été enregistrée et est en attente de validation par un administrateur.";
                redirect('/admin/wallet/requests');
                exit;
            }

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

    /**
     * Handle payment return from gateway (user redirected back)
     */
    public function paymentReturn()
    {
        $requestId = $_GET['request_id'] ?? null;

        if (!$requestId) {
            $_SESSION['flash_error'] = 'ID de demande manquant';
            redirect('/admin/wallet/requests');
            exit;
        }

        $request = WalletTopupRequest::find($requestId);

        if (!$request) {
            $_SESSION['flash_error'] = 'Demande de recharge introuvable';
            redirect('/admin/wallet/requests');
            exit;
        }

        // Check if payment is already completed
        if ($request->status === 'completed') {
            $_SESSION['flash_success'] = 'Paiement déjà confirmé. Votre wallet a été crédité.';
            redirect('/admin/wallet/requests');
            exit;
        }

        // Payment is processing - show waiting message
        $_SESSION['flash_info'] = 'Votre paiement est en cours de traitement. Vous recevrez une confirmation par email une fois le paiement validé.';
        redirect('/admin/wallet/requests');
        exit;
    }

    /**
     * Handle payment cancellation
     */
    public function paymentCancel()
    {
        $requestId = $_GET['request_id'] ?? null;

        if (!$requestId) {
            $_SESSION['flash_error'] = 'ID de demande manquant';
            redirect('/admin/wallet/requests');
            exit;
        }

        $request = WalletTopupRequest::find($requestId);

        if (!$request) {
            $_SESSION['flash_error'] = 'Demande de recharge introuvable';
            redirect('/admin/wallet/requests');
            exit;
        }

        // Mark as cancelled if still pending/processing
        if (in_array($request->status, ['pending', 'processing'])) {
            $request->update([
                'status' => 'cancelled',
                'gateway_status' => 'CANCELLED'
            ]);
        }

        $_SESSION['flash_warning'] = 'Paiement annulé. Vous pouvez faire une nouvelle tentative si vous le souhaitez.';
        redirect('/admin/wallet/topup');
        exit;
    }

    /**
     * Handle payment webhook/callback from gateway
     * This is called by the payment gateway to notify payment status
     */
    public function handlePaymentCallback($gatewayCode)
    {
        // Get raw POST data
        $rawData = file_get_contents('php://input');
        $payload = json_decode($rawData, true);

        // If JSON decode failed, try to use $_POST
        if (!$payload) {
            $payload = $_POST;
        }

        // Log the webhook for debugging
        error_log("Payment webhook received from {$gatewayCode}: " . $rawData);

        try {
            // Process callback through gateway manager
            $result = $this->gatewayManager->handleCallback($gatewayCode, $payload);

            if (!$result['valid']) {
                error_log("Invalid webhook signature from {$gatewayCode}: " . ($result['error'] ?? 'Unknown error'));
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
                exit;
            }

            // Extract transaction details
            $transactionId = $result['transaction_id'] ?? null;
            $status = $result['status'] ?? 'unknown';
            $gatewayReference = $result['gateway_reference'] ?? null;

            if (!$transactionId) {
                error_log("No transaction ID in webhook from {$gatewayCode}");
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing transaction ID']);
                exit;
            }

            // Find the topup request
            $request = WalletTopupRequest::where('gateway_transaction_id', $transactionId)->first();

            if (!$request) {
                error_log("Topup request not found for transaction {$transactionId}");
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Request not found']);
                exit;
            }

            // Update request with gateway response
            $request->update([
                'gateway_response' => json_encode($result),
                'gateway_status' => strtoupper($status)
            ]);

            // Handle based on status
            if ($status === 'success' || $status === 'completed') {
                // Payment successful - credit the wallet
                if ($request->status !== 'completed') {
                    $request->update(['status' => 'approved']);

                    // Add credit to wallet
                    $this->walletService->addCredit(
                        $request->user_id,
                        $request->amount,
                        'Recharge via ' . $gatewayCode . ' (Ref: ' . ($gatewayReference ?? $transactionId) . ')'
                    );

                    // Mark as completed
                    $request->markAsCompleted();

                    // Send approval notification
                    $newBalance = $this->walletService->getBalance($request->user_id);
                    $this->notificationService->sendRequestApprovedNotification($request, $newBalance);

                    error_log("Wallet credited successfully for request {$request->id}");
                }

                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Payment processed']);
                exit;

            } elseif ($status === 'failed' || $status === 'declined') {
                // Payment failed
                $request->update(['status' => 'failed']);

                error_log("Payment failed for request {$request->id}");
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Payment failed recorded']);
                exit;

            } else {
                // Unknown status - keep processing
                error_log("Unknown payment status '{$status}' for request {$request->id}");
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Status recorded']);
                exit;
            }

        } catch (\Exception $e) {
            error_log("Error processing webhook from {$gatewayCode}: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}
