<?php

namespace App\Core\Middleware;

use Modules\Users\Models\User;
use Modules\ApiKeys\Models\ApiKey;

class ApiAuthMiddleware
{
    /**
     * Handle API authentication via API key
     *
     * Expects Authorization header: Bearer {api_key}
     * or api_key parameter in query string or POST data
     *
     * @param mixed $request Request data (array from Router)
     * @param callable $next Next middleware/handler
     * @return mixed
     */
    public function handle($request, $next)
    {
        $apiKeyString = $this->extractApiKey();

        // Debug log
        error_log("ApiAuthMiddleware: Extracted key = " . ($apiKeyString ? substr($apiKeyString, 0, 20) . '...' : 'NULL'));

        if (!$apiKeyString) {
            $this->sendUnauthorizedResponse('API key required');
            return false;
        }

        // Find API key in api_keys table
        $apiKey = ApiKey::where('key', $apiKeyString)
            ->where('is_active', 1)
            ->first();

        // Debug log
        error_log("ApiAuthMiddleware: Key found in DB = " . ($apiKey ? 'YES (ID=' . $apiKey->id . ')' : 'NO'));

        if (!$apiKey) {
            $this->sendUnauthorizedResponse('Invalid API key');
            return false;
        }

        // Check if API key is valid (not expired)
        if (!$apiKey->isValid()) {
            $this->sendUnauthorizedResponse('API key expired or inactive');
            return false;
        }

        // Check IP whitelist if configured
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$apiKey->isIpAllowed($clientIp)) {
            $this->sendUnauthorizedResponse('IP address not allowed');
            return false;
        }

        // Get the user associated with this API key
        $user = $apiKey->user()->first();

        if (!$user || !$user->is_active) {
            $this->sendUnauthorizedResponse('User account inactive');
            return false;
        }

        // Update last used timestamp
        $apiKey->recordUsage();

        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store authenticated user in session for RBAC middlewares
        $_SESSION['api_user_id'] = $user->id;
        $_SESSION['user_id'] = $user->id; // For RBAC compatibility

        // Store user and API key in request for controller access
        $_REQUEST['api_user'] = $user;
        $_REQUEST['api_key'] = $apiKey;

        return $next($request);
    }

    /**
     * Extract API key from request
     */
    private function extractApiKey(): ?string
    {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.+)/', $auth, $matches)) {
                return $matches[1];
            }
        }

        // Check query parameter
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        // Check POST data
        if (isset($_POST['api_key'])) {
            return $_POST['api_key'];
        }

        // Check JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['api_key'])) {
            return $input['api_key'];
        }

        return null;
    }

    /**
     * Send unauthorized response
     */
    private function sendUnauthorizedResponse(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => $message
        ]);
        exit;
    }
}
