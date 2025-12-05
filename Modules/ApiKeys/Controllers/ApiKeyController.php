<?php

namespace Modules\ApiKeys\Controllers;

use App\Core\Application;
use Modules\ApiKeys\Models\ApiKey;
use Modules\Users\Models\User;

class ApiKeyController
{
    /**
     * Display a listing of the user's API keys
     */
    public function index()
    {
        $app = Application::getInstance();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        // Get user's API keys (no soft delete anymore)
        $apiKeys = ApiKey::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Check if there's a newly created key in session (show once)
        $newKey = $_SESSION['new_api_key'] ?? null;
        unset($_SESSION['new_api_key']);

        echo view('apikeys/apikeys/index', [
            'title' => 'API Keys Management',
            'apiKeys' => $apiKeys,
            'newKey' => $newKey
        ]);
    }

    /**
     * Generate and store a new API key
     */
    public function store()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            exit;
        }

        // Validate input
        $name = trim($_POST['name'] ?? '');
        $prefix = trim($_POST['prefix'] ?? 'sk_live');
        $permissions = $_POST['permissions'] ?? [];
        $ipWhitelist = trim($_POST['ip_whitelist'] ?? '');
        $expiresAt = trim($_POST['expires_at'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = 'API key name is required';
            redirect('/admin/system-api-keys');
            exit;
        }

        // Generate the API key
        $apiKey = ApiKey::generateKey($prefix);

        // Create the API key record
        $apiKeyModel = new ApiKey();
        $apiKeyModel->user_id = $userId;
        $apiKeyModel->name = $name;
        $apiKeyModel->key = $apiKey;
        $apiKeyModel->prefix = $prefix;
        $apiKeyModel->ip_whitelist = $ipWhitelist;
        $apiKeyModel->expires_at = $expiresAt ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null;
        $apiKeyModel->is_active = 1;

        // Set permissions if provided
        if (!empty($permissions)) {
            $apiKeyModel->setPermissionsArray($permissions);
        }

        $apiKeyModel->save();

        // Store the key in session to show once
        $_SESSION['new_api_key'] = [
            'key' => $apiKey,
            'name' => $name
        ];

        $_SESSION['flash_success'] = 'API key created successfully. Copy it now, it won\'t be shown again!';
        redirect('/admin/system-api-keys');
        exit;
    }

    /**
     * Revoke an API key (permanent delete)
     */
    public function revoke(array $params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        $keyId = $params['id'] ?? null;

        if (!$userId || !$keyId) {
            redirect('/admin/system-api-keys');
            exit;
        }

        $apiKey = ApiKey::where('id', $keyId)
            ->where('user_id', $userId)
            ->first();

        if ($apiKey) {
            // Permanent delete
            if (method_exists($apiKey, 'forceDelete')) {
                $apiKey->forceDelete(); // Permanent delete (bypass soft delete)
            } else {
                $apiKey->delete(); // Fallback
            }

            $_SESSION['flash_success'] = 'Clé API révoquée et supprimée avec succès';
        } else {
            $_SESSION['flash_error'] = 'API key not found';
        }

        redirect('/admin/system-api-keys');
        exit;
    }

    /**
     * Activate an API key (set is_active to 1)
     */
    public function activate(array $params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        $keyId = $params['id'] ?? null;

        if (!$userId || !$keyId) {
            redirect('/admin/system-api-keys');
            exit;
        }

        $apiKey = ApiKey::where('id', $keyId)
            ->where('user_id', $userId)
            ->first();

        if ($apiKey) {
            $apiKey->is_active = 1;
            $apiKey->save();

            $_SESSION['flash_success'] = 'Clé API activée avec succès';
        } else {
            $_SESSION['flash_error'] = 'Clé API non trouvée';
        }

        redirect('/admin/system-api-keys');
        exit;
    }

    /**
     * Deactivate an API key (set is_active to 0)
     */
    public function deactivate(array $params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        $keyId = $params['id'] ?? null;

        if (!$userId || !$keyId) {
            redirect('/admin/system-api-keys');
            exit;
        }

        $apiKey = ApiKey::where('id', $keyId)
            ->where('user_id', $userId)
            ->first();

        if ($apiKey) {
            $apiKey->is_active = 0;
            $apiKey->save();

            $_SESSION['flash_success'] = 'Clé API désactivée avec succès';
        } else {
            $_SESSION['flash_error'] = 'Clé API non trouvée';
        }

        redirect('/admin/system-api-keys');
        exit;
    }

    /**
     * Delete an API key permanently
     */
    public function destroy(array $params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        $keyId = $params['id'] ?? null;

        if (!$userId || !$keyId) {
            redirect('/admin/system-api-keys');
            exit;
        }

        $apiKey = ApiKey::where('id', $keyId)
            ->where('user_id', $userId)
            ->first();

        if ($apiKey) {
            $apiKey->delete();
            $_SESSION['flash_success'] = 'API key deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'API key not found';
        }

        redirect('/admin/system-api-keys');
        exit;
    }
}
