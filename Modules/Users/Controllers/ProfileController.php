<?php

namespace Modules\Users\Controllers;

use Modules\Users\Models\User;

class ProfileController
{
    /**
     * Show user profile
     */
    public function show()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'Utilisateur non trouvé';
            redirect('/admin/dashboard');
            return;
        }

        return view('users/profile', [
            'title' => 'Mon Profil',
            'user' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function update()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'Utilisateur non trouvé';
            redirect('/admin/dashboard');
            return;
        }

        // Update basic info
        if (isset($_POST['first_name'])) {
            $user->first_name = $_POST['first_name'];
        }
        if (isset($_POST['last_name'])) {
            $user->last_name = $_POST['last_name'];
        }
        if (isset($_POST['email'])) {
            $user->email = $_POST['email'];
        }
        if (isset($_POST['username'])) {
            $user->username = $_POST['username'];
        }

        // Update password if provided
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $user->password = password_hash($_POST['new_password'], PASSWORD_ARGON2ID);
            } else {
                $_SESSION['flash_error'] = 'Les mots de passe ne correspondent pas';
                redirect('/admin/profile');
                return;
            }
        }

        $user->save();

        $_SESSION['flash_success'] = 'Profil mis à jour avec succès';
        redirect('/admin/profile');
    }

    /**
     * Show change password page
     */
    public function showChangePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        return view('users/change-password', [
            'title' => 'Changer le mot de passe'
        ]);
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'Utilisateur non trouvé';
            redirect('/admin/dashboard');
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Verify current password
        if (!password_verify($currentPassword, $user->password)) {
            $_SESSION['flash_error'] = 'Mot de passe actuel incorrect';
            redirect('/admin/profile/change-password');
            return;
        }

        // Check new passwords match
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'Les nouveaux mots de passe ne correspondent pas';
            redirect('/admin/profile/change-password');
            return;
        }

        // Check password strength
        if (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'Le mot de passe doit contenir au moins 8 caractères';
            redirect('/admin/profile/change-password');
            return;
        }

        // Update password
        $user->password = password_hash($newPassword, PASSWORD_ARGON2ID);
        $user->save();

        $_SESSION['flash_success'] = 'Mot de passe changé avec succès';
        redirect('/admin/profile');
    }
}
