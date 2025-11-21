<?php

// Mock config function
function config($key, $default = null)
{
    return '/sunuframework2';
}

// Mock url function
function url(string $path = ''): string
{
    $baseUrl = config('app.url', '/sunuframework2');
    $baseUrl = rtrim($baseUrl, '/');

    if (!empty($path) && $path[0] !== '/') {
        $path = '/' . $path;
    }

    return $baseUrl . $path;
}

// Vite function from helpers.php (copied for testing)
function vite(string $path)
{
    $devServer = 'http://localhost:5173';
    $manifestPath = __DIR__ . '/public/build/.vite/manifest.json';

    // Check if dev server is running
    $isDev = false;
    $handle = @fsockopen('localhost', 5173, $errno, $errstr, 0.1);
    if ($handle) {
        $isDev = true;
        fclose($handle);
    }

    if ($isDev) {
        return '<script type="module" src="' . $devServer . '/@vite/client"></script>' .
            '<script type="module" src="' . $devServer . '/' . $path . '"></script>';
    }

    if (!file_exists($manifestPath)) {
        return '<!-- Vite Manifest not found. Run npm run build -->';
    }

    $manifest = json_decode(file_get_contents($manifestPath), true);
    $file = $manifest[$path]['file'] ?? null;
    $css = $manifest[$path]['css'] ?? [];

    if (!$file) {
        return "<!-- Asset $path not found in manifest -->";
    }

    $html = '';
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    if ($ext === 'css') {
        $html .= '<link rel="stylesheet" href="' . url('public/build/' . $file) . '">';
    } else {
        $html .= '<script type="module" src="' . url('public/build/' . $file) . '"></script>';
    }

    foreach ($css as $cssFile) {
        $html .= '<link rel="stylesheet" href="' . url('public/build/' . $cssFile) . '">';
    }

    return $html;
}

echo "Testing vite('js/app.js'):\n";
echo vite('js/app.js');
echo "\n\n";

echo "Testing vite('css/app.css'):\n";
echo vite('css/app.css');
echo "\n";
