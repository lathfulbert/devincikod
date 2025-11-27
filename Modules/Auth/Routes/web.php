<?php

use Modules\Auth\Controllers\AuthController;

/** @var \App\Core\Routing\Router $router */

$router->get('/', function () {
    redirect('/login');
});

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout']);
