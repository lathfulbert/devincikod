<?php

namespace Modules\__MODULE__\Widgets;

use App\Core\Widget\AbstractWidget;

/**
 * Widget __WIDGET_NAME__
 *
 * [Description détaillée du widget]
 *
 * Type: [stat|kpi|chart|list|info|alert|trend|activity]
 * Permission: [module.permission.view ou null]
 * Valeur ajoutée: [Expliquer pourquoi ce widget est utile]
 */
class __WIDGET_NAME__Widget extends AbstractWidget
{
    /**
     * Nom unique du widget
     * Convention: module.nom_widget (en minuscules, avec points ou underscores)
     */
    protected string $name = '__module__.__widget_name__';

    /**
     * Type de widget
     * Valeurs: stat, kpi, chart, list, info, alert, trend, activity
     */
    protected string $type = 'stat';

    /**
     * Permission requise pour afficher ce widget
     * null = accessible à tous les utilisateurs authentifiés
     * Format: module.action.view
     */
    protected ?string $permission = '__module__.view';

    /**
     * Activer le système de cache
     */
    protected bool $cacheable = true;

    /**
     * Durée du cache en secondes
     * Recommandations:
     * - Données statiques: 3600 (1 heure)
     * - Données peu changeantes: 600 (10 minutes)
     * - Données fréquentes: 300 (5 minutes)
     * - Données temps réel: 60 (1 minute)
     */
    protected int $cacheDuration = 300;

    /**
     * Configuration supplémentaire du widget
     */
    protected array $config = [
        'title' => 'Titre du widget',
        'description' => 'Description courte',
        'icon' => 'activity', // Nom d'icône Feather Icons
        'color' => 'primary', // primary, success, info, warning, danger
        'order' => 1 // Ordre d'affichage (optionnel)
    ];

    /**
     * Calcule et retourne les données du widget
     *
     * Cette méthode est appelée automatiquement par getData()
     * Elle doit retourner un tableau avec la structure standard
     *
     * @return array
     */
    protected function calculateData(): array
    {
        // ========================================
        // 1. RÉCUPÉRATION DES DONNÉES
        // ========================================

        // Exemple avec Model
        // $data = VotreModel::query()->where('condition', 'value')->count();

        // Exemple avec Database directe
        // $db = Database::getInstance();
        // $result = $db->query("SELECT COUNT(*) as total FROM table")->fetch();
        // $data = $result['total'] ?? 0;

        // Exemple avec Service
        // $service = Container::getInstance()->make(VotreService::class);
        // $data = $service->getStatistics();

        // ========================================
        // 2. CALCULS MÉTIER
        // ========================================

        // Exemple: Calcul de tendance
        // $trend = $this->calculateTrend($currentValue, $previousValue);

        // Exemple: Calcul de pourcentage
        // $percentage = $total > 0 ? ($value / $total) * 100 : 0;

        // ========================================
        // 3. GESTION DES CAS LIMITES
        // ========================================

        // Vérifier les valeurs nulles, diviser par zéro, etc.

        // ========================================
        // 4. RETOUR DES DONNÉES
        // ========================================

        return [
            // REQUIS: Titre affiché en haut du widget
            'title' => 'Titre du widget',

            // REQUIS: Valeur principale (nombre, texte, etc.)
            // Utilisez number_format() pour les grands nombres
            'value' => '1,234',

            // OPTIONNEL: Description complémentaire
            'description' => 'Information additionnelle',

            // OPTIONNEL: Icône Feather Icons
            // https://feathericons.com/
            'icon' => 'activity',

            // OPTIONNEL: Tendance (évolution)
            'trend' => [
                'percentage' => 15.5, // Pourcentage de variation (float)
                'direction' => 'up' // 'up', 'down', ou 'stable'
            ],

            // OPTIONNEL: Méta-données additionnelles
            // Utilisé pour des données spécifiques au type de widget
            'meta' => [
                // Pour type 'list':
                'items' => [
                    ['title' => 'Item 1', 'value' => '100'],
                    ['title' => 'Item 2', 'value' => '200']
                ],

                // Pour type 'activity':
                'activities' => [
                    ['title' => 'Action 1', 'time' => 'Il y a 5 min', 'icon' => 'check'],
                    ['title' => 'Action 2', 'time' => 'Il y a 10 min', 'icon' => 'send']
                ],

                // Pour type 'alert':
                'severity' => 'warning', // info, warning, danger, success

                // Pour type 'chart':
                'chart_type' => 'line',
                'chart_data' => [
                    'labels' => ['Jan', 'Fév', 'Mar'],
                    'datasets' => [[
                        'label' => 'Données',
                        'data' => [10, 20, 30]
                    ]]
                ],

                // Liens et actions
                'url' => '/module/action',
                'actions' => [
                    ['label' => 'Voir plus', 'url' => '/some/url']
                ],

                // Données brutes (pour débug ou usage avancé)
                'raw_data' => []
            ]
        ];
    }

    // ========================================
    // MÉTHODES HELPER (OPTIONNELLES)
    // ========================================

    /**
     * Calcule la tendance entre deux valeurs
     *
     * @param int|float $current Valeur actuelle
     * @param int|float $previous Valeur précédente
     * @return array
     */
    private function calculateTrend($current, $previous): array
    {
        if ($previous == 0) {
            return [
                'percentage' => 0,
                'direction' => 'stable'
            ];
        }

        $percentage = (($current - $previous) / $previous) * 100;
        $direction = $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'stable');

        return [
            'percentage' => round(abs($percentage), 1),
            'direction' => $direction
        ];
    }

    /**
     * Formate un nombre avec séparateurs de milliers
     *
     * @param int|float $number
     * @param int $decimals
     * @return string
     */
    private function formatNumber($number, int $decimals = 0): string
    {
        return number_format($number, $decimals, ',', ' ');
    }

    /**
     * Calcule le temps écoulé depuis une date
     *
     * @param string $datetime
     * @return string
     */
    private function timeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'À l\'instant';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "Il y a {$minutes} minute" . ($minutes > 1 ? 's' : '');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Il y a {$hours} heure" . ($hours > 1 ? 's' : '');
        } else {
            $days = floor($diff / 86400);
            return "Il y a {$days} jour" . ($days > 1 ? 's' : '');
        }
    }

    // ========================================
    // SURCHARGE DE MÉTHODES (OPTIONNEL)
    // ========================================

    /**
     * Personnaliser la clé de cache
     * Utile si vous voulez un cache par utilisateur ou par contexte
     *
     * @return string
     */
    /*
    public function getCacheKey(): string
    {
        // Exemple: cache par utilisateur
        $userId = $_SESSION['user_id'] ?? 0;
        return 'widget:' . $this->getName() . ':user:' . $userId;

        // Exemple: cache global
        return 'widget:' . $this->getName();
    }
    */

    /**
     * Logique de permission personnalisée
     * En plus de la vérification RBAC standard
     *
     * @param mixed $user
     * @return bool
     */
    /*
    public function canView($user): bool
    {
        // Vérifier d'abord la permission standard
        if (!parent::canView($user)) {
            return false;
        }

        // Ajouter des vérifications supplémentaires
        // Exemple: vérifier un abonnement actif
        return $user->hasActiveSubscription();

        // Exemple: vérifier un statut spécifique
        return $user->status === 'premium';
    }
    */
}

// ========================================
// CHECKLIST AVANT DÉPLOIEMENT
// ========================================
/*
□ Le widget étend AbstractWidget
□ Les propriétés $name, $type, $permission sont définies
□ La méthode calculateData() est implémentée
□ Les données retournées respectent la structure standard
□ Les requêtes SQL utilisent des requêtes préparées (pas d'injection SQL)
□ Les données sensibles sont protégées par permissions
□ Le cache est configuré selon la fréquence de mise à jour des données
□ Les cas limites sont gérés (division par zéro, valeurs nulles, etc.)
□ Le DocBlock est complet et précis
□ Les exceptions potentielles sont gérées (try/catch)
□ Les grands nombres sont formatés avec number_format()
□ Le widget est testé avec un utilisateur ayant les permissions
□ Le widget est testé avec un utilisateur sans permissions
□ La documentation interne est à jour
*/
