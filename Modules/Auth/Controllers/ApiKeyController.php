<?php

namespace Modules\Auth\Controllers;

use App\Core\Application;
use Modules\Users\Models\User;

class ApiKeyController
{
    /**
     * Display API key management page
     */
    public function index()
    {
        $app = Application::getInstance();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        echo view('auth/api/index', [
            'user' => $user,
            'hasApiKey' => !empty($user->api_key)
        ]);
    }

    /**
     * Generate new API key
     */
    public function generate()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            redirect('/admin/api-keys');
            exit;
        }

        // Generate secure API key
        $apiKey = bin2hex(random_bytes(32));

        // Update user
        $user->update([
            'api_key' => $apiKey,
            'api_key_created_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_success'] = 'API key generated successfully';
        redirect('/admin/api-keys');
        exit;
    }

    /**
     * Regenerate API key
     */
    public function regenerate()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            redirect('/admin/api-keys');
            exit;
        }

        // Generate new API key
        $apiKey = bin2hex(random_bytes(32));

        // Update user
        $user->update([
            'api_key' => $apiKey,
            'api_key_created_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_success'] = 'API key regenerated successfully. Please update your applications with the new key.';
        redirect('/admin/api-keys');
        exit;
    }

    /**
     * Revoke API key
     */
    public function revoke()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            redirect('/admin/api-keys');
            exit;
        }

        // Revoke API key
        $user->update([
            'api_key' => null,
            'api_key_created_at' => null
        ]);

        $_SESSION['flash_success'] = 'API key revoked successfully';
        redirect('/admin/api-keys');
        exit;
    }

    /**
     * Display API documentation
     */
    public function docs()
    {
        echo view('auth/api/docs', []);
    }
}
