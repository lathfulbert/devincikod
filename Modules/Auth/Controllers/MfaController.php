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
        $methods = $this->mfaManager->getAvailableMethods($userId);

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

            // For SMS/Email - setup is complete, show success
            $message = $result['message'] ?? 'Méthode MFA configurée avec succès';

            if ($methodType === 'sms') {
                $message = '📱 SMS OTP activé ! Un code de vérification a été envoyé au ' . ($result['phone'] ?? 'numéro configuré');
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
}
