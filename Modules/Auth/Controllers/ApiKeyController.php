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
            $_SESSION['flash']['danger'][] = 'Vous devez être connecté pour générer une clé API';
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash']['danger'][] = 'Utilisateur non trouvé';
            redirect('/admin/apikeys');
            exit;
        }

        try {
            // Generate secure API key
            $apiKey = bin2hex(random_bytes(32));

            // Update user directly via database
            $db = \App\Core\Database\Database::getInstance();
            $db->query(
                "UPDATE users SET api_key = ?, api_key_created_at = NOW() WHERE id = ?",
                [$apiKey, $userId]
            );

            $_SESSION['flash']['success'][] = 'Clé API générée avec succès !';
        } catch (\Exception $e) {
            $_SESSION['flash']['danger'][] = 'Erreur lors de la génération de la clé API : ' . $e->getMessage();
        }

        redirect('/admin/apikeys');
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
            $_SESSION['flash']['danger'][] = 'Utilisateur non trouvé';
            redirect('/admin/apikeys');
            exit;
        }

        // Generate new API key
        $apiKey = bin2hex(random_bytes(32));

        // Update user
        $user->update([
            'api_key' => $apiKey,
            'api_key_created_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash']['success'][] = 'Clé API régénérée avec succès. Veuillez mettre à jour vos applications avec la nouvelle clé.';
        redirect('/admin/apikeys');
        exit;
    }

    /**
     * Revoke API key
     */
    public function revoke()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['flash']['danger'][] = 'Vous devez être connecté pour révoquer une clé API';
            redirect('/login');
            exit;
        }

        $user = User::find($userId);

        if (!$user) {
            $_SESSION['flash']['danger'][] = 'Utilisateur non trouvé';
            redirect('/admin/apikeys');
            exit;
        }

        try {
            // Revoke API key directly via database
            $db = \App\Core\Database\Database::getInstance();
            $db->query(
                "UPDATE users SET api_key = NULL, api_key_created_at = NULL WHERE id = ?",
                [$userId]
            );

            $_SESSION['flash']['success'][] = 'Clé API révoquée avec succès';
        } catch (\Exception $e) {
            $_SESSION['flash']['danger'][] = 'Erreur lors de la révocation de la clé API : ' . $e->getMessage();
        }

        redirect('/admin/apikeys');
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
