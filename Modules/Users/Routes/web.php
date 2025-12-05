<?php

use Modules\Users\Controllers\AdminUserController;
use Modules\Users\Controllers\ProfileController;

/** @var \App\Core\Routing\Router $router */

// User Profile
$router->get('/admin/profile', [ProfileController::class, 'show']);
$router->post('/admin/profile/update', [ProfileController::class, 'update']);
$router->get('/admin/profile/change-password', [ProfileController::class, 'showChangePassword']);
$router->post('/admin/profile/change-password', [ProfileController::class, 'changePassword']);

// Users Management
$router->group(['prefix' => '/admin/users', 'middleware' => ['auth', 'can:admin.users.view']], function ($router) {
    $router->get('', [AdminUserController::class, 'index'])->name('admin.users.index');
    $router->get('/create', [AdminUserController::class, 'create'])->middleware('can:admin.users.create')->name('admin.users.create');
    $router->post('/store', [AdminUserController::class, 'store'])->middleware('can:admin.users.create');
    $router->get('/{id}/edit', [AdminUserController::class, 'edit'])->middleware('can:admin.users.edit')->name('admin.users.edit');
    $router->post('/{id}/update', [AdminUserController::class, 'update'])->middleware('can:admin.users.edit');
    $router->post('/{id}/delete', [AdminUserController::class, 'delete'])->middleware('can:admin.users.delete');
});
