<?php

namespace Modules\Auth\Controllers;

use App\Core\Application;
use App\Core\Database\Database;
use Modules\Auth\Models\User;

class PasswordResetController
{
    /**
     * Display forgot password form
     */
    public function forgotPassword()
    {
        $app = Application::getInstance();
        echo $app->view->render('auth/forgot-password', []);
    }

    /**
     * Send password reset link
     */
    public function sendResetLink()
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['flash_error'] = 'Email is required';
            redirect('/forgot-password');
            exit;
        }

        // Check if user exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Don't reveal if email exists or not for security
            $_SESSION['flash_success'] = 'If the email exists, a password reset link has been sent';
            redirect('/forgot-password');
            exit;
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store reset token in database
        $db = Database::getInstance();

        // Delete old tokens for this email
        $db->query("DELETE FROM password_resets WHERE email = ?", [$email]);

        // Insert new token
        $db->query(
            "INSERT INTO password_resets (email, token, expires_at, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
            [$email, $token, $expiresAt]
        );

        // Generate reset link
        $resetLink = url('/reset-password?token=' . $token . '&email=' . urlencode($email));

        // TODO: Send email with reset link
        // For now, we'll just show it in flash message (for development)
        $_SESSION['flash_success'] = 'Password reset link: ' . $resetLink;

        redirect('/forgot-password');
        exit;
    }

    /**
     * Display reset password form
     */
    public function resetPassword()
    {
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';

        if (empty($token) || empty($email)) {
            $_SESSION['flash_error'] = 'Invalid reset link';
            redirect('/login');
            exit;
        }

        // Verify token
        $db = Database::getInstance();
        $reset = $db->query(
            "SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()",
            [$email, $token]
        )->fetch();

        if (!$reset) {
            $_SESSION['flash_error'] = 'Invalid or expired reset link';
            redirect('/login');
            exit;
        }

        $app = Application::getInstance();
        echo $app->view->render('auth/reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Update password with reset token
     */
    public function updatePassword()
    {
        $token = $_POST['token'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($email) || empty($password) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'All fields are required';
            redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            exit;
        }

        // Validate password
        if (strlen($password) < 6) {
            $_SESSION['flash_error'] = 'Password must be at least 6 characters';
            redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['flash_error'] = 'Password confirmation does not match';
            redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            exit;
        }

        // Verify token
        $db = Database::getInstance();
        $reset = $db->query(
            "SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()",
            [$email, $token]
        )->fetch();

        if (!$reset) {
            $_SESSION['flash_error'] = 'Invalid or expired reset link';
            redirect('/login');
            exit;
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            redirect('/login');
            exit;
        }

        // Update password
        $user->update([
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        // Delete used token
        $db->query("DELETE FROM password_resets WHERE email = ?", [$email]);

        $_SESSION['flash_success'] = 'Password reset successfully. You can now login with your new password.';
        redirect('/login');
        exit;
    }
}
