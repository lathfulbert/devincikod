<?php

namespace App\Core\Widget\Contracts;

/**
 * Interface WidgetInterface
 *
 * Contrat que tous les widgets doivent implémenter
 * pour garantir une structure uniforme à travers le framework.
 */
interface WidgetInterface
{
    /**
     * Retourne les données du widget au format standardisé
     *
     * @return array Structure:
     * [
     *   'title' => string,
     *   'value' => string|int|float,
     *   'description' => string,
     *   'icon' => string,
     *   'trend' => ['percentage' => float, 'direction' => 'up|down|stable'],
     *   'meta' => array (données additionnelles spécifiques au widget)
     * ]
     */
    public function getData(): array;

    /**
     * Retourne le nom unique du widget
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Retourne le type du widget
     * (stat, kpi, chart, list, info, alert, trend, activity)
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Retourne la permission requise pour afficher ce widget
     * Format: module.widget.widget_name.view
     *
     * @return string|null null si pas de permission requise
     */
    public function getPermission(): ?string;

    /**
     * Détermine si le widget peut être mis en cache
     *
     * @return bool
     */
    public function isCacheable(): bool;

    /**
     * Retourne la durée de cache en secondes
     *
     * @return int 0 = pas de cache
     */
    public function getCacheDuration(): int;

    /**
     * Retourne la clé de cache unique pour ce widget
     *
     * @return string
     */
    public function getCacheKey(): string;

    /**
     * Valide que l'utilisateur a les permissions nécessaires
     *
     * @param mixed $user
     * @return bool
     */
    public function canView($user): bool;

    /**
     * Retourne la configuration du widget
     *
     * @return array
     */
    public function getConfig(): array;
}
