<?php

namespace Modules\Auth\Providers;

use Modules\Users\Models\User;
use Modules\Auth\Models\OauthAccount;
use Modules\Auth\Models\AuthLog;
use Modules\Settings\Models\Setting;

/**
 * OAuth Provider
 * Handles OAuth2 login (Google, GitHub, etc.)
 */
class OauthProvider implements AuthProviderInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'oauth';
    }

    /**
     * Authenticate via OAuth
     * 
     * @param mixed $identifier Provider Name (e.g. 'google')
     * @param mixed $credential Auth Code from callback
     * @return array|false
     */
    public function verify($identifier, $credential)
    {
        // This is handled differently than other providers
        // Usually handled by a dedicated service or library (e.g. HybridAuth or custom)
        // For now, we will implement a basic flow
        return false;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function setup(int $userId, array $data): array
    {
        // Link existing account
        return [];
    }

    /**
     * Get provider configuration
     */
    public function getProviderConfig(string $provider): ?array
    {
        $clientId = Setting::get("auth_oauth_{$provider}_client_id");
        $clientSecret = Setting::get("auth_oauth_{$provider}_client_secret");

        if (!$clientId || !$clientSecret) {
            return null;
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $this->getRedirectUri($provider)
        ];
    }

    public function getRedirectUri(string $provider): string
    {
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return $baseUrl . "/auth/oauth/callback/{$provider}";
    }

    /**
     * Get Authorization URL
     */
    public function getAuthUrl(string $provider): string
    {
        $config = $this->getProviderConfig($provider);
        if (!$config) {
            throw new \Exception("Provider $provider not configured");
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        switch ($provider) {
            case 'google':
                return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
                    'client_id' => $config['client_id'],
                    'redirect_uri' => $config['redirect_uri'],
                    'response_type' => 'code',
                    'scope' => 'email profile',
                    'state' => $state,
                    'access_type' => 'offline'
                ]);

            case 'github':
                return "https://github.com/login/oauth/authorize?" . http_build_query([
                    'client_id' => $config['client_id'],
                    'redirect_uri' => $config['redirect_uri'],
                    'scope' => 'user:email',
                    'state' => $state
                ]);

            default:
                throw new \Exception("Provider $provider not supported");
        }
    }

    /**
     * Handle Callback
     */
    public function handleCallback(string $provider, string $code, string $state): array
    {
        if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
            throw new \Exception("Invalid state");
        }

        $config = $this->getProviderConfig($provider);
        if (!$config) {
            throw new \Exception("Provider $provider not configured");
        }

        switch ($provider) {
            case 'google':
                return $this->handleGoogleCallback($code, $config);
            case 'github':
                return $this->handleGithubCallback($code, $config);
            default:
                throw new \Exception("Provider $provider not supported");
        }
    }

    private function handleGoogleCallback(string $code, array $config): array
    {
        // Exchange code for token
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        $response = $this->makeRequest($url, $data);
        if (isset($response['error'])) {
            throw new \Exception("Google Auth Error: " . $response['error_description']);
        }

        $accessToken = $response['access_token'];

        // Get User Info
        $userInfo = $this->makeRequest('https://www.googleapis.com/oauth2/v3/userinfo', null, $accessToken, 'GET');

        return [
            'provider' => 'google',
            'provider_user_id' => $userInfo['sub'],
            'email' => $userInfo['email'],
            'name' => $userInfo['name'],
            'avatar' => $userInfo['picture'] ?? null,
            'token' => $response
        ];
    }

    private function handleGithubCallback(string $code, array $config): array
    {
        // Exchange code for token
        $url = 'https://github.com/login/oauth/access_token';
        $data = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri']
        ];

        $response = $this->makeRequest($url, $data, null, 'POST', ['Accept: application/json']);

        if (isset($response['error'])) {
            throw new \Exception("GitHub Auth Error: " . ($response['error_description'] ?? $response['error']));
        }

        $accessToken = $response['access_token'];

        // Get User Info
        $userInfo = $this->makeRequest('https://api.github.com/user', null, $accessToken, 'GET', ['User-Agent: LathDevinci']);

        // Get Email if private
        if (empty($userInfo['email'])) {
            $emails = $this->makeRequest('https://api.github.com/user/emails', null, $accessToken, 'GET', ['User-Agent: LathDevinci']);
            foreach ($emails as $email) {
                if ($email['primary'] && $email['verified']) {
                    $userInfo['email'] = $email['email'];
                    break;
                }
            }
        }

        return [
            'provider' => 'github',
            'provider_user_id' => $userInfo['id'],
            'email' => $userInfo['email'],
            'name' => $userInfo['name'] ?? $userInfo['login'],
            'avatar' => $userInfo['avatar_url'] ?? null,
            'token' => $response
        ];
    }

    private function makeRequest(string $url, ?array $postData = null, ?string $token = null, string $method = 'POST', array $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_POST, false); // Reset POST
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        if ($token) {
            $headers[] = "Authorization: Bearer $token";
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Disable SSL verify for local dev if needed (use with caution)
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("CURL Error: $error");
        }

        return json_decode($result, true);
    }
}
