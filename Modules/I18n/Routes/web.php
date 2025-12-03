<?php

use Modules\I18n\Controllers\I18nController;

/** @var \App\Core\Routing\Router $router */

$controller = new I18nController();

$router->get('/admin/i18n', [$controller, 'index']);
$router->get('/admin/i18n/create', [$controller, 'create']);
$router->post('/admin/i18n/create', [$controller, 'store']);
$router->get('/admin/i18n/edit', [$controller, 'edit']);
$router->post('/admin/i18n/edit', [$controller, 'update']);
$router->post('/admin/i18n/delete', [$controller, 'delete']);
$router->post('/admin/i18n/clear-cache', [$controller, 'clearCache']);
$router->get('/admin/i18n/export', [$controller, 'export']);
$router->post('/admin/i18n/import', [$controller, 'import']);
$router->get('/admin/i18n/set-locale', [$controller, 'setLocale']);
