<?php

namespace App\Core\Middleware;

use Modules\Users\Models\User;

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
        $apiKey = $this->extractApiKey();

        if (!$apiKey) {
            $this->sendUnauthorizedResponse('API key required');
            return false;
        }

        // Find user by API key
        $user = User::where('api_key', $apiKey)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            $this->sendUnauthorizedResponse('Invalid API key');
            return false;
        }

        // Store authenticated user in session for RBAC middlewares
        $_SESSION['api_user_id'] = $user->id;
        $_SESSION['user_id'] = $user->id; // For RBAC compatibility

        // Store user in request for controller access
        $_REQUEST['api_user'] = $user;

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
