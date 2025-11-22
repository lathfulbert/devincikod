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

// File Manager API Routes
$router->group(['prefix' => '/api/files', 'middleware' => ['secure_upload']], function ($router) {
    $router->post('/upload', [FileController::class, 'upload']);
    $router->get('/list', [FileController::class, 'list']);
    $router->delete('/delete', [FileController::class, 'delete']);
});
