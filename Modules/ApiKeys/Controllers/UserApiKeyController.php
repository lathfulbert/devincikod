<?php

namespace Modules\ApiKeys\Controllers;

use Modules\ApiKeys\Models\ApiKey;
use Modules\Users\Models\User;

class UserApiKeyController
{
    /**
     * Show user's personal API key
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = User::find($userId);

        // Get or create user's API key (SoftDeletes excludes deleted automatically)
        $apiKey = ApiKey::query()
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->orderBy('id', 'DESC') // Get most recent key
            ->first();

        // Debug: Check what's in apiKey
        if ($apiKey) {
            error_log("ApiKey created_at: " . ($apiKey->created_at ?? 'NULL'));
            error_log("ApiKey attributes: " . json_encode(get_object_vars($apiKey)));
        }

        // Debug avancé : log de la tentative de rendu de la vue
        $viewName = 'ApiKeys/apikeys/user';
        echo view($viewName, [
            'title' => 'Ma Clé API',
            'user' => $user,
            'apiKey' => $apiKey
        ]);
    }

    /**
     * Generate new API key for user (UPDATE if exists, INSERT if not)
     */
    public function generate()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];

        // Check if user already has a key (including soft-deleted)
        $apiKey = ApiKey::query()
            ->where('user_id', $userId)
            ->first();

        // Generate new key
        $key = 'sk_' . bin2hex(random_bytes(32));
        $hashedKey = hash('sha256', $key);

        if ($apiKey) {
            // UPDATE existing key
            $apiKey->key = $hashedKey;
            $apiKey->is_active = 1;
            $apiKey->deleted_at = null; // Un-delete if was soft-deleted
            $apiKey->updated_at = date('Y-m-d H:i:s');
            $apiKey->save();
        } else {
            // INSERT new key
            $apiKey = new ApiKey();
            $apiKey->user_id = $userId;
            $apiKey->name = 'Personal API Key';
            $apiKey->key = $hashedKey;
            $apiKey->is_active = 1;
            $apiKey->created_at = date('Y-m-d H:i:s');
            $apiKey->save();
        }

        $_SESSION['new_api_key'] = $key; // Show once (plain text)
        $_SESSION['flash_success'] = 'Nouvelle clé API générée avec succès';

        redirect('/admin/apikeys');
    }

    /**
     * Revoke user's API key (permanent delete)
     */
    public function revoke()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/auth/login');
            return;
        }

        $userId = $_SESSION['user_id'];

        $apiKey = ApiKey::query()
            ->where('user_id', $userId)
            ->first();

        if ($apiKey) {
            // Permanent delete
            if (method_exists($apiKey, 'forceDelete')) {
                $apiKey->forceDelete(); // Permanent delete (bypass soft delete)
            } else {
                $apiKey->delete(); // Fallback to regular delete
            }
            $_SESSION['flash_success'] = 'Clé API révoquée avec succès';
        }

        redirect('/admin/apikeys');
    }
}
