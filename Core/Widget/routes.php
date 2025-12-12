<?php

/**
 * Routes API pour le système de widgets
 *
 * À inclure dans votre fichier de routes principal ou via un système de découverte automatique
 *
 * Exemple d'inclusion:
 * require_once __DIR__ . '/Core/Widget/routes.php';
 */

use App\Core\Widget\WidgetController;

/** @var \App\Core\Routing\Router $router */

// Groupe de routes API pour les widgets
$router->group([
    'prefix' => '/api/widgets',
    'middleware' => ['auth']
], function ($router) {

    // GET /api/widgets - Liste tous les widgets disponibles pour l'utilisateur
    $router->get('', [WidgetController::class, 'index'])
        ->name('api.widgets.index');

    // GET /api/widgets/module/{module} - Liste les widgets d'un module spécifique
    $router->get('/module/{module}', [WidgetController::class, 'moduleWidgets'])
        ->name('api.widgets.module');

    // POST /api/widgets/batch - Récupère plusieurs widgets en une requête
    $router->post('/batch', [WidgetController::class, 'batch'])
        ->name('api.widgets.batch');

    // POST /api/widgets/discover - Force la redécouverte des widgets (admin only)
    $router->post('/discover', [WidgetController::class, 'discover'])
        ->middleware('can:admin.access')
        ->name('api.widgets.discover');

    // GET /api/widgets/{widget} - Récupère les données d'un widget spécifique
    $router->get('/{widget}', [WidgetController::class, 'show'])
        ->name('api.widgets.show');

    // POST /api/widgets/{widget}/clear-cache - Invalide le cache d'un widget
    $router->post('/{widget}/clear-cache', [WidgetController::class, 'clearCache'])
        ->name('api.widgets.clear_cache');
});
