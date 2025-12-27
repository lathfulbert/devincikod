<?php

namespace Modules\Auth\Controllers;

use App\Core\Application;
use Modules\Users\Models\User;

class ProfileController
{
    /**
     * Display user profile edit form
     */
    public function edit()
    {
        $app = Application::getInstance();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        return view('auth/profile/edit', [
            'user' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function update()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            redirect('/admin/profile');
            exit;
        }

        // Validate input
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');

        if (empty($username) || empty($email)) {
            $_SESSION['flash_error'] = 'Username and email are required';
            redirect('/admin/profile');
            exit;
        }

        // Check if username already exists (for other users)
        $existingUser = User::where('username', $username)
            ->where('id', '!=', $userId)
            ->first();

        if ($existingUser) {
            $_SESSION['flash_error'] = 'Username already taken';
            redirect('/admin/profile');
            exit;
        }

        // Check if email already exists (for other users)
        $existingEmail = User::where('email', $email)
            ->where('id', '!=', $userId)
            ->first();

        if ($existingEmail) {
            $_SESSION['flash_error'] = 'Email already taken';
            redirect('/admin/profile');
            exit;
        }

        // Handle avatar upload
        $avatarPath = $user->avatar;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/avatars/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                // Delete old avatar if exists
                if ($user->avatar && file_exists(dirname(dirname(dirname(__DIR__))) . '/public/' . $user->avatar)) {
                    unlink(dirname(dirname(dirname(__DIR__))) . '/public/' . $user->avatar);
                }
                $avatarPath = 'uploads/avatars/' . $fileName;
            }
        }

        // Update user
        $user->update([
            'username' => $username,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'avatar' => $avatarPath
        ]);

        $_SESSION['flash_success'] = 'Profile updated successfully';
        redirect('/admin/profile');
        exit;
    }

    /**
     * Display change password form
     */
    public function changePassword()
    {
        $app = Application::getInstance();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        return view('auth/profile/change-password', []);
    }

    /**
     * Update password
     */
    public function updatePassword()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            redirect('/admin/profile/change-password');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'All fields are required';
            redirect('/admin/profile/change-password');
            exit;
        }

        // Verify current password
        if (!password_verify($currentPassword, $user->password)) {
            $_SESSION['flash_error'] = 'Current password is incorrect';
            redirect('/admin/profile/change-password');
            exit;
        }

        // Validate new password
        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'New password must be at least 6 characters';
            redirect('/admin/profile/change-password');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'Password confirmation does not match';
            redirect('/admin/profile/change-password');
            exit;
        }

        // Update password
        $user->update([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        $_SESSION['flash_success'] = 'Password changed successfully';
        redirect('/admin/profile/change-password');
        exit;
    }
}
