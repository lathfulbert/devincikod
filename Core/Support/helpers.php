<?php

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $app = \App\Core\Application::getInstance();
        $baseUrl = $app->config->get('app.url', '/sunuframework2'); // Default to /sunuframework2 if not set
        
        // Ensure base url doesn't have trailing slash
        $baseUrl = rtrim($baseUrl, '/');
        
        // Ensure path starts with /
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $baseUrl . $path;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
