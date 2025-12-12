<?php

/**
 * Configuration du système de widgets
 *
 * Ce fichier contient toutes les configurations par défaut du système de widgets
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Découverte automatique
    |--------------------------------------------------------------------------
    |
    | Active la découverte automatique des widgets au démarrage de l'application
    |
    */
    'auto_discover' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configuration du système de cache des widgets
    |
    */
    'cache' => [
        // Activer le cache globalement
        'enabled' => true,

        // Durée par défaut du cache en secondes
        'default_duration' => 300, // 5 minutes

        // Répertoire de stockage du cache
        'path' => __DIR__ . '/../../storage/cache/widgets',

        // Préfixe pour les clés de cache
        'prefix' => 'widget:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Configuration des permissions pour les widgets
    |
    */
    'permissions' => [
        // Vérifier automatiquement les permissions via RBAC
        'auto_check' => true,

        // Permission requise pour découvrir les widgets
        'discover' => 'admin.access',

        // Permission requise pour invalider les caches
        'clear_cache' => 'admin.settings.edit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rendu
    |--------------------------------------------------------------------------
    |
    | Configuration du rendu des widgets
    |
    */
    'render' => [
        // Colonnes par défaut pour l'affichage en grille
        'default_columns' => 3,

        // Classes CSS par défaut pour les cards
        'default_card_class' => 'col-lg-4 col-md-6 mb-4',

        // Afficher les widgets non autorisés (avec message)
        'show_unauthorized' => false,

        // Afficher un message si aucun widget n'est disponible
        'show_empty' => false,

        // Framework CSS (bootstrap5, tailwind, etc.)
        'css_framework' => 'bootstrap5',
    ],

    /*
    |--------------------------------------------------------------------------
    | Types de widgets
    |--------------------------------------------------------------------------
    |
    | Types de widgets supportés par le système
    |
    */
    'types' => [
        'stat' => 'Statistique / KPI',
        'kpi' => 'Indicateur de performance',
        'chart' => 'Graphique',
        'list' => 'Liste',
        'info' => 'Information',
        'alert' => 'Alerte',
        'trend' => 'Tendance',
        'activity' => 'Activité récente',
    ],

    /*
    |--------------------------------------------------------------------------
    | Icônes
    |--------------------------------------------------------------------------
    |
    | Configuration des icônes (Feather Icons par défaut)
    |
    */
    'icons' => [
        // Bibliothèque d'icônes utilisée
        'library' => 'feather', // feather, fontawesome, etc.

        // Icônes par défaut selon le type de widget
        'defaults' => [
            'stat' => 'activity',
            'kpi' => 'trending-up',
            'chart' => 'bar-chart-2',
            'list' => 'list',
            'info' => 'info',
            'alert' => 'alert-circle',
            'trend' => 'trending-up',
            'activity' => 'clock',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    |
    | Configuration de l'API REST des widgets
    |
    */
    'api' => [
        // Préfixe des routes API
        'prefix' => '/api/widgets',

        // Middleware appliqués aux routes API
        'middleware' => ['auth'],

        // Limite de widgets par requête batch
        'batch_limit' => 20,

        // Format de réponse (json, xml)
        'response_format' => 'json',

        // Inclure la configuration du widget dans la réponse
        'include_config' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Développement et Debug
    |--------------------------------------------------------------------------
    |
    | Options utiles en développement
    |
    */
    'debug' => [
        // Mode debug (affiche les erreurs détaillées)
        'enabled' => false,

        // Logger les erreurs de widgets
        'log_errors' => true,

        // Afficher le temps d'exécution des widgets
        'show_execution_time' => false,

        // Désactiver le cache en mode debug
        'disable_cache' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance
    |--------------------------------------------------------------------------
    |
    | Options de performance
    |
    */
    'performance' => [
        // Charger les widgets de manière asynchrone (lazy loading)
        'lazy_load' => false,

        // Timeout maximum pour le calcul des données (secondes)
        'calculation_timeout' => 30,

        // Limiter le nombre de widgets affichés simultanément
        'max_widgets_per_page' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules exclus
    |--------------------------------------------------------------------------
    |
    | Modules à exclure de la découverte automatique
    |
    */
    'excluded_modules' => [
        // 'ExampleModule',
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets personnalisés
    |--------------------------------------------------------------------------
    |
    | Enregistrement manuel de widgets personnalisés
    |
    */
    'custom_widgets' => [
        // Format: 'module' => ['WidgetClass1', 'WidgetClass2']
        // 'Users' => [
        //     \Modules\Users\Widgets\CustomWidget::class,
        // ],
    ],
];
