<?php

namespace App\Core\Middleware;

use Modules\Users\Models\User;
use Modules\ApiKeys\Models\ApiKey;

/**
 * API Middleware - Inspired by Laravel Sanctum
 *
 * This middleware handles API authentication for all modules.
 * It validates API keys and sets up the authenticated user context.
 *
 * Usage:
 * - Apply to routes via 'api' middleware alias
 * - Routes in routes/api.php automatically get this middleware
 * - Can be used in any module's routes
 *
 * Authentication Methods:
 * 1. Authorization header: "Bearer {api_key}"
 * 2. Query parameter: ?api_key={api_key}
 * 3. POST data: api_key={api_key}
 * 4. JSON body: {"api_key": "{api_key}"}
 */
class ApiMiddleware
{
    /**
     * Handle API authentication
     *
     * @param mixed $request Request data
     * @param callable $next Next middleware/handler
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Extract API key from various sources
        $apiKeyString = $this->extractApiKey();

        if (!$apiKeyString) {
            return $this->unauthorized('API key required');
        }

        // Hash the API key for database lookup (keys are stored hashed)
        $hashedKey = hash('sha256', $apiKeyString);

        // Find and validate API key using hashed version
        $apiKey = ApiKey::where('key', $hashedKey)
            ->where('is_active', 1)
            ->first();

        if (!$apiKey) {
            return $this->unauthorized('Invalid API key');
        }

        // Check if API key is still valid (not expired)
        if (!$apiKey->isValid()) {
            return $this->unauthorized('API key expired or inactive');
        }

        // Check IP whitelist if configured
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$apiKey->isIpAllowed($clientIp)) {
            return $this->unauthorized('IP address not allowed');
        }

        // Get the associated user
        $user = $apiKey->user()->getResults();

        if (!$user || !$user->is_active) {
            return $this->unauthorized('User account inactive');
        }

        // Record API key usage
        $apiKey->recordUsage();

        // Set up authenticated context for the request
        $this->setAuthenticatedContext($user, $apiKey);

        // Continue to next middleware/handler
        return $next($request);
    }

    /**
     * Extract API key from request
     * Checks multiple sources in order of priority
     *
     * @return string|null
     */
    protected function extractApiKey(): ?string
    {
        // 1. Check Authorization header (Bearer token)
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)/i', $headers['Authorization'], $matches)) {
                return trim($matches[1]);
            }
        }

        // 2. Check query parameter
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        // 3. Check POST data
        if (isset($_POST['api_key'])) {
            return $_POST['api_key'];
        }

        // 4. Check JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['api_key'])) {
            return $input['api_key'];
        }

        return null;
    }

    /**
     * Set up authenticated context for API requests
     * Makes user and API key available throughout the request lifecycle
     *
     * @param User $user
     * @param ApiKey $apiKey
     */
    protected function setAuthenticatedContext(User $user, ApiKey $apiKey): void
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store user in session (for compatibility with existing code)
        $_SESSION['api_user_id'] = $user->id;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['authenticated_via'] = 'api';

        // Store in request context (accessible in controllers)
        $_REQUEST['api_user'] = $user;
        $_REQUEST['api_key'] = $apiKey;
        $_REQUEST['auth_user'] = $user; // Standard auth context
    }

    /**
     * Send unauthorized JSON response
     *
     * @param string $message Error message
     * @return bool
     */
    protected function unauthorized(string $message): bool
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
