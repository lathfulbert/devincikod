<?php

namespace App\Core\Middleware;

use Modules\ApiKeys\Models\ApiKey;
use Modules\ApiKeys\Models\ApiRequestLog;

/**
 * API Key Authentication Middleware
 *
 * Authenticates API requests using Bearer token (API key)
 * Usage: Add this middleware to routes that require API key authentication
 */
class ApiKeyMiddleware
{
    private float $startTime;
    private ?ApiKey $keyModel = null;
    private int $statusCode = 200;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Handle the middleware
     *
     * @param string|null $requiredPermission Optional permission to check
     * @return bool
     */
    public function handle(?string $requiredPermission = null): bool
    {
        $apiKey = $this->extractApiKey();
        error_log('[APIKEY] Clé reçue : ' . var_export($apiKey, true));

        if (!$apiKey) {
            $this->statusCode = 401;
            $this->sendUnauthorizedResponse('API key required');
            return false;
        }

        // Les clés sont stockées en SHA256, il faut donc hasher la clé reçue
        $hashedKey = hash('sha256', $apiKey);
        error_log('[APIKEY] Hash recherché : ' . $hashedKey);
        $this->keyModel = ApiKey::where('key', $hashedKey)->first();

        if (!$this->keyModel) {
            $this->statusCode = 401;
            $this->sendUnauthorizedResponse('Invalid API key');
            return false;
        }

        // Check if key is valid
        if (!$this->keyModel->isValid()) {
            $this->statusCode = 401;
            $this->sendUnauthorizedResponse('API key is invalid, expired or revoked');
            return false;
        }

        // Check IP whitelist
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$this->keyModel->isIpAllowed($clientIp)) {
            $this->statusCode = 403;
            $this->sendUnauthorizedResponse('IP address not allowed for this API key');
            return false;
        }

        // Check permission if required
        if ($requiredPermission && !$this->keyModel->hasPermission($requiredPermission)) {
            $this->statusCode = 403;
            $this->sendForbiddenResponse('Insufficient permissions for this endpoint');
            return false;
        }

        // Update last used timestamp (asynchronously to avoid slowing down the request)
        $this->keyModel->recordUsage();

        // Store the API key model and user in global context for use in controllers
        $_SERVER['API_KEY'] = $this->keyModel;
        $_SERVER['API_USER_ID'] = $this->keyModel->user_id;

        // Log the successful request
        $this->logRequest();

        // Facturation automatique de l'appel API (exemple)
        try {
            $endpoint = $_SERVER['REQUEST_URI'] ?? '';
            $billingService = app(\Modules\Wallet\Services\BillingService::class);
            $billingService->chargeApiUsage(
                $this->keyModel->user_id,
                $this->keyModel->id,
                $endpoint,
                [] // Ajoutez ici des critères si besoin (volume, type, etc.)
            );
        } catch (\Throwable $e) {
            error_log('[APIKEY] Facturation API échouée : ' . $e->getMessage());
            // Optionnel : return false ou laisser passer la requête même si la facturation échoue
        }

        return true;
    }

    /**
     * Extract API key from request
     * Checks: Authorization header, query parameter, POST parameter
     */
    private function extractApiKey(): ?string
    {
        // 1. Check Authorization header (Bearer token)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // 2. Check X-API-Key header
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }

        // 3. Check query parameter
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        // 4. Check POST parameter
        if (isset($_POST['api_key'])) {
            return $_POST['api_key'];
        }

        return null;
    }

    /**
     * Log the API request
     */
    private function logRequest(string $responseBody = null): void
    {
        try {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $endpoint = parse_url($requestUri, PHP_URL_PATH);

            // Get request body
            $rawBody = file_get_contents('php://input');
            $requestBody = $rawBody ?: null;

            // Get request headers
            $requestHeaders = $this->getRequestHeaders();

            // Calculate response time
            $responseTime = (int)((microtime(true) - $this->startTime) * 1000);

            ApiRequestLog::logRequest([
                'api_key_id' => $this->keyModel ? $this->keyModel->id : null,
                'user_id' => $this->keyModel ? $this->keyModel->user_id : null,
                'endpoint' => $endpoint,
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'request_headers' => $requestHeaders,
                'request_body' => $requestBody,
                'status_code' => $this->statusCode,
                'response_body' => $responseBody,
                'response_time' => $responseTime,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'referer' => $_SERVER['HTTP_REFERER'] ?? null,
                'requested_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't let logging errors break the request
            error_log('Failed to log API request: ' . $e->getMessage());
        }
    }

    /**
     * Get all request headers
     */
    private function getRequestHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    /**
     * Send unauthorized response (401)
     */
    private function sendUnauthorizedResponse(string $message): void
    {
        $responseBody = json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => $message
        ]);

        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo $responseBody;

        // Log the failed request
        $this->logRequest($responseBody);

        exit;
    }

    /**
     * Send forbidden response (403)
     */
    private function sendForbiddenResponse(string $message): void
    {
        $responseBody = json_encode([
            'success' => false,
            'error' => 'Forbidden',
            'message' => $message
        ]);

        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo $responseBody;

        // Log the failed request
        $this->logRequest($responseBody);

        exit;
    }

    /**
     * Invoke method for middleware usage
     */
    public function __invoke(?string $requiredPermission = null)
    {
        return $this->handle($requiredPermission);
    }
}
