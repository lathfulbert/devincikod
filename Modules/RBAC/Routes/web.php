<?php

use Modules\RBAC\Controllers\RoleController;
use Modules\RBAC\Controllers\PermissionController;

/** @var \App\Core\Routing\Router $router */

// Roles Management
$router->group(['prefix' => '/admin/roles', 'middleware' => ['auth', 'can:admin.roles.view']], function ($router) {
    $router->get('', [RoleController::class, 'index'])->name('admin.roles.index');
    $router->get('/create', [RoleController::class, 'create'])->middleware('can:admin.roles.create')->name('admin.roles.create');
    $router->post('/store', [RoleController::class, 'store'])->middleware('can:admin.roles.create');
    $router->get('/{id}/edit', [RoleController::class, 'edit'])->middleware('can:admin.roles.edit')->name('admin.roles.edit');
    $router->post('/{id}/update', [RoleController::class, 'update'])->middleware('can:admin.roles.edit');
    $router->post('/{id}/delete', [RoleController::class, 'delete'])->middleware('can:admin.roles.delete');
});

// Permissions Management
$router->group(['prefix' => '/admin/permissions', 'middleware' => ['auth', 'role:admin']], function ($router) {
    $router->get('', [PermissionController::class, 'index'])->name('admin.permissions.index');
    $router->get('/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
    $router->post('/store', [PermissionController::class, 'store']);
    $router->get('/{id}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    $router->post('/{id}/update', [PermissionController::class, 'update']);
    $router->post('/{id}/delete', [PermissionController::class, 'delete']);
});
