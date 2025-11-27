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
