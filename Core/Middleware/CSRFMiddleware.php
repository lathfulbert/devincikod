<?php

namespace App\Core\Middleware;

use App\Core\Security\CSRF;

class CSRFMiddleware
{
    /**
     * Vérifie le token CSRF pour les requêtes non-GET
     */
    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Ignorer les requêtes GET, HEAD, OPTIONS
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return;
        }

        $csrf = CSRF::getInstance();
        $token = $_POST[CSRF::getTokenName()] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        // Debug logging
        $logFile = app()->getBasePath() . '/storage/logs/csrf_debug.log';
        $debugInfo = sprintf(
            "[%s] Method: %s | Token from POST: %s | Token from Session: %s | POST data: %s\n",
            date('Y-m-d H:i:s'),
            $method,
            $token ?? 'NULL',
            $_SESSION[CSRF::getTokenName()] ?? 'NULL',
            json_encode($_POST)
        );
        file_put_contents($logFile, $debugInfo, FILE_APPEND);

        if (!$csrf->validateToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed. Votre session a peut-être expiré. Veuillez rafraîchir la page.');
        }
    }

    /**
     * Vérifie si la route est exclue de la vérification CSRF
     */
    private function isExcluded(string $uri): bool
    {
        $excludedRoutes = [
            '/api/*', // Exclure les routes API (utiliser un autre système d'auth)
        ];

        foreach ($excludedRoutes as $pattern) {
            if (fnmatch($pattern, $uri)) {
                return true;
            }
        }

        return false;
    }
}
