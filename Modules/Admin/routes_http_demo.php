<?php

/**
 * HTTP Client Test Routes
 * 
 * Add these routes to test the HTTP Client
 */

// In your routes file (e.g., Modules/Admin/routes.php)

use Modules\Admin\Controllers\HttpClientDemoController;

// HTTP Client Demo Routes
$router->get('/admin/http-test/github', [HttpClientDemoController::class, 'githubUser']);
$router->get('/admin/http-test/post', [HttpClientDemoController::class, 'postExample']);
$router->get('/admin/http-test/auth', [HttpClientDemoController::class, 'authenticatedRequest']);
$router->get('/admin/http-test/retry', [HttpClientDemoController::class, 'withRetry']);
$router->get('/admin/http-test/weather', [HttpClientDemoController::class, 'weatherExample']);
$router->get('/admin/http-test/status', [HttpClientDemoController::class, 'checkApiStatus']);
