<?php

use App\Core\Files\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

/**
 * Auth Module Routes
 */

use Modules\Auth\Controllers\AuthController;

/** @var \App\Core\Routing\Router $router */

// Auth routes
$router->get('/', [AuthController::class, 'showLogin']);

// File Manager API Routes
$router->group(['prefix' => '/api/files', 'middleware' => ['secure_upload', 'api_auth']], function ($router) {
    $router->post('/upload', [FileController::class, 'upload'])->middleware('can:files.upload');
    $router->get('/list', [FileController::class, 'list'])->middleware('can:files.list');
    $router->delete('/delete', [FileController::class, 'delete'])->middleware('can:files.delete');
});
