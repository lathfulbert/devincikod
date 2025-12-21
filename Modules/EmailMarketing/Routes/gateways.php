<?php
use Modules\EmailMarketing\Controllers\GatewayController;

$authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];
$rbacMiddleware = [new \Modules\RBAC\Middleware\CheckPermission(), 'handle', 'manage_email_gateways'];

$router->group(['prefix' => '/admin/email-marketing/gateways', 'middleware' => [$authMiddleware, $rbacMiddleware]], function($router) {
    $router->get('', [GatewayController::class, 'index'])->name('admin.email-marketing.gateways.index');
    $router->get('/create', [GatewayController::class, 'create'])->name('admin.email-marketing.gateways.create');
    $router->post('/store', [GatewayController::class, 'store'])->name('admin.email-marketing.gateways.store');
    $router->get('/{name}/edit', [GatewayController::class, 'edit'])->name('admin.email-marketing.gateways.edit');
    $router->post('/{name}/update', [GatewayController::class, 'update'])->name('admin.email-marketing.gateways.update');
    $router->get('/{name}/delete', [GatewayController::class, 'delete'])->name('admin.email-marketing.gateways.delete');
});
