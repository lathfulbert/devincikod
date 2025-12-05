<?php

namespace Modules\Auth\Middleware;

/**
 * Require MFA Middleware
 * Enforces MFA for protected routes
 */
class RequireMfa
{
    public function handle()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return false;
        }

        $userId = $_SESSION['user_id'];

        // Check if MFA is required and not yet completed
        $mfaManager = new \Modules\Auth\Services\MfaManager();

        if ($mfaManager->isRequired($userId) && !isset($_SESSION['mfa_verified'])) {
            $_SESSION['flash_error'] = 'Please complete MFA verification';
            redirect('/auth/mfa/challenge');
            return false;
        }

        return true;
    }
}
