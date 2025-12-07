<?php

namespace Modules\Auth\Controllers;

use Modules\Auth\Services\MfaManager;
use Modules\Auth\Services\AuditLogger;

/**
 * MFA Controller
 * Handles MFA setup, challenge, and verification
 */
class MfaController
{
    private MfaManager $mfaManager;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->mfaManager = new MfaManager();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * Show MFA challenge page
     */
    public function showChallenge()
    {
        if (!isset($_SESSION['mfa_required']) || !$_SESSION['mfa_required']) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['mfa_user_id'];
        $methods = $this->mfaManager->getAvailableMethods($userId);

        echo view('auth/mfa/challenge', [
            'methods' => $methods
        ]);
    }

    /**
     * Verify MFA code
     */
    public function verifyChallenge()
    {
        if (!isset($_SESSION['mfa_required']) || !$_SESSION['mfa_required']) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['mfa_user_id'];
        $methodType = $_POST['method'] ?? null;
        $code = $_POST['code'] ?? null;

        if (!$methodType || !$code) {
            $_SESSION['flash_error'] = 'Method and code are required';
            redirect('/auth/mfa/challenge');
            return;
        }

        // Verify MFA code
        $isValid = $this->mfaManager->verify($userId, $methodType, $code);

        if (!$isValid) {
            $_SESSION['flash_error'] = 'Invalid verification code';
            redirect('/auth/mfa/challenge');
            return;
        }

        // MFA successful - complete login
        $user = \Modules\Users\Models\User::find($userId)->toArray();
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $userId;
        unset($_SESSION['mfa_user_id']);
        unset($_SESSION['mfa_required']);

        $_SESSION['flash_success'] = 'Login successful!';
        redirect('/admin/dashboard');
    }

    /**
     * Send OTP code (for SMS/Email)
     */
    public function sendOtp()
    {
        if (!isset($_SESSION['mfa_required'])) {
            echo json_encode(['success' => false, 'message' => 'Not authorized']);
            return;
        }

        $userId = $_SESSION['mfa_user_id'];
        $methodType = $_POST['method'] ?? null;

        if (!$methodType) {
            echo json_encode(['success' => false, 'message' => 'Method is required']);
            return;
        }

        $success = $this->mfaManager->sendOtp($userId, $methodType);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Code sent successfully' : 'Failed to send code'
        ]);
    }

    /**
     * Show MFA settings page
     */
    public function showSettings()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $methods = $this->mfaManager->getAllUserMethods($userId);

        echo view('auth/mfa/settings', [
            'methods' => $methods
        ]);
    }

    /**
     * Show SMS setup page
     */
    public function showSmsSetup()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        echo view('auth/mfa/sms_setup', [
            'title' => 'Activer SMS OTP'
        ]);
    }

    /**
     * Setup MFA method
     */
    public function setup()
    {
        error_log("======MFA Setup called======");

        if (!isset($_SESSION['user_id'])) {
            error_log("No user_id in session");
            $_SESSION['flash_error'] = 'Vous devez être connecté';
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $methodType = $_POST['method'] ?? null;

        error_log("User ID: $userId, Method: $methodType");

        if (!$methodType) {
            $_SESSION['flash_error'] = 'La méthode est requise';
            redirect('/auth/mfa/settings');
            return;
        }

        try {
            $data = [];

            // Add method-specific data
            if ($methodType === 'sms' && isset($_POST['phone'])) {
                $data['phone'] = $_POST['phone'];
            }

            error_log("Calling setupMethod...");
            $result = $this->mfaManager->setupMethod($userId, $methodType, $data);
            error_log("Setup result: " . json_encode($result));

            // For TOTP, show QR code
            if ($methodType === 'totp') {
                $_SESSION['totp_setup'] = $result;
                error_log("Redirecting to TOTP setup");
                redirect('/auth/mfa/setup/totp');
                return;
            }

            // For SMS, redirect to verification page
            if ($methodType === 'sms') {
                $_SESSION['sms_setup_phone'] = $result['phone'] ?? $data['phone'];
                $message = '📱 SMS OTP activé ! Un code de vérification a été envoyé au ' . ($result['phone'] ?? 'numéro configuré');
                $_SESSION['flash_success'] = $message;
                redirect('/auth/mfa/setup/sms/verify');
                return;
            } elseif ($methodType === 'email') {
                $message = '📧 Email OTP activé ! Un code de vérification vous sera envoyé à chaque connexion';
            }

            $_SESSION['flash_success'] = $message;
            redirect('/auth/mfa/settings');
        } catch (\Exception $e) {
            error_log("Exception: " . $e->getMessage());
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/auth/mfa/settings');
        }
    }

    /**
     * Show TOTP setup page (with QR code)
     */
    public function showTotpSetup()
    {
        if (!isset($_SESSION['totp_setup'])) {
            redirect('/auth/mfa/settings');
            return;
        }

        $setupData = $_SESSION['totp_setup'];

        echo view('auth/mfa/totp_setup', [
            'qr_code_url' => $setupData['qr_code_url'],
            'secret' => $setupData['secret'],
            'backup_codes' => $setupData['backup_codes']
        ]);
    }

    /**
     * Verify TOTP setup
     */
    public function verifyTotpSetup()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $code = $_POST['code'] ?? null;

        if (!$code) {
            $_SESSION['flash_error'] = 'Le code de vérification est requis';
            redirect('/auth/mfa/setup/totp');
            return;
        }

        // Verify setup
        $provider = $this->mfaManager->getProvider('totp');
        $isValid = $provider->verifySetup($userId, $code);

        if (!$isValid) {
            $_SESSION['flash_error'] = 'Code invalide. Réessayez.';
            redirect('/auth/mfa/setup/totp');
            return;
        }

        unset($_SESSION['totp_setup']);
        $_SESSION['flash_success'] = 'TOTP configuré avec succès!';
        redirect('/auth/mfa/settings');
    }

    /**
     * Show SMS verification page
     */
    public function showSmsVerification()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        echo view('auth/mfa/sms_verify', [
            'phone' => $_SESSION['sms_setup_phone'] ?? 'votre numéro'
        ]);
    }

    /**
     * Verify SMS setup
     */
    public function verifySmsSetup()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $code = $_POST['code'] ?? null;

        if (!$code) {
            $_SESSION['flash_error'] = 'Le code de vérification est requis';
            redirect('/auth/mfa/setup/sms/verify');
            return;
        }

        // We need to manually verify the OTP from session and update the DB
        // because the provider's verify() method checks for is_verified=1 which is not yet true
        $sessionKey = "sms_otp_{$userId}";
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION['flash_error'] = 'Session expirée. Veuillez recommencer.';
            redirect('/auth/mfa/setup/sms');
            return;
        }

        $otpData = $_SESSION[$sessionKey];
        if (!hash_equals($otpData['code_hash'], hash('sha256', $code))) {
            $_SESSION['flash_error'] = 'Code invalide.';
            redirect('/auth/mfa/setup/sms/verify');
            return;
        }

        // Update DB to set is_verified = 1
        \Modules\Auth\Models\UserMfaSetup::where('user_id', $userId)
            ->where('method_type', 'sms')
            ->update(['is_verified' => 1]);

        unset($_SESSION['sms_setup_phone']);
        unset($_SESSION[$sessionKey]);

        $_SESSION['flash_success'] = 'SMS OTP configuré et vérifié avec succès!';
        redirect('/auth/mfa/settings');
    }

    /**
     * Disable MFA method
     */
    public function disable()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $methodType = $_POST['method'] ?? null;

        if (!$methodType) {
            $_SESSION['flash_error'] = 'La méthode est requise';
            redirect('/auth/mfa/settings');
            return;
        }

        $success = $this->mfaManager->disableMethod($userId, $methodType);

        if ($success) {
            $_SESSION['flash_success'] = 'Méthode MFA désactivée';
        } else {
            $_SESSION['flash_error'] = 'Échec de la désactivation';
        }

        redirect('/auth/mfa/settings');
    }

    /**
     * Show SMS OTP Configuration (Admin)
     */
    public function showSmsConfig()
    {
        // Check admin permission (assuming 'admin' role or similar check)
        // For now, just check if logged in, but in production should be stricter
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        // Get current settings
        $currentSenderId = \Modules\Settings\Models\Setting::get('auth_sms_sender_id', 'AUTH');
        $currentGateway = \Modules\Settings\Models\Setting::get('auth_sms_gateway_id', 'auto');

        // Get available gateways
        $gateways = \Modules\Settings\Models\SmsGateway::where('is_active', 1)->get();

        // Get available sender names (all system sender names)
        $senderNames = \Modules\SmsCore\Models\SenderName::where('status', 'approved')->get();

        echo view('auth/admin/sms_config', [
            'currentSenderId' => $currentSenderId,
            'currentGateway' => $currentGateway,
            'gateways' => $gateways,
            'senderNames' => $senderNames
        ]);
    }

    /**
     * Save SMS OTP Configuration (Admin)
     */
    public function saveSmsConfig()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/auth/sms-config');
            return;
        }

        // Check admin permission
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $senderId = $_POST['sender_id'] ?? 'AUTH';
        $gatewayCode = $_POST['gateway_code'] ?? 'auto';

        // Save settings
        \Modules\Settings\Models\Setting::set('auth_sms_sender_id', $senderId);
        \Modules\Settings\Models\Setting::set('auth_sms_gateway_id', $gatewayCode);

        $_SESSION['flash_success'] = 'Configuration SMS OTP mise à jour avec succès';
        redirect('/admin/auth/sms-config');
    }
}
