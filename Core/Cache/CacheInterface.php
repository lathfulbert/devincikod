<?php

namespace App\Core\Cache;

/**
 * Interface CacheInterface
 * 
 * Contrat standard pour tous les drivers de cache.
 * Inspiré de PSR-16 (Simple Cache) avec extensions personnalisées.
 */
interface CacheInterface
{
    /**
     * Récupère une valeur du cache
     *
     * @param string $key La clé du cache
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed La valeur en cache ou la valeur par défaut
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Stocke une valeur dans le cache
     *
     * @param string $key La clé du cache
     * @param mixed $value La valeur à stocker
     * @param int|null $ttl Durée de vie en secondes (null = infini)
     * @return bool True si succès, false sinon
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Vérifie si une clé existe dans le cache
     *
     * @param string $key La clé à vérifier
     * @return bool True si la clé existe et n'est pas expirée
     */
    public function has(string $key): bool;

    /**
     * Supprime une valeur du cache
     *
     * @param string $key La clé à supprimer
     * @return bool True si succès, false sinon
     */
    public function delete(string $key): bool;

    /**
     * Vide complètement le cache
     *
     * @return bool True si succès, false sinon
     */
    public function clear(): bool;

    /**
     * Récupère plusieurs valeurs du cache
     *
     * @param array $keys Tableau de clés
     * @param mixed $default Valeur par défaut pour les clés manquantes
     * @return array Tableau associatif [clé => valeur]
     */
    public function getMultiple(array $keys, mixed $default = null): array;

    /**
     * Stocke plusieurs valeurs dans le cache
     *
     * @param array $values Tableau associatif [clé => valeur]
     * @param int|null $ttl Durée de vie en secondes
     * @return bool True si toutes les opérations réussissent
     */
    public function setMultiple(array $values, ?int $ttl = null): bool;

    /**
     * Supprime plusieurs valeurs du cache
     *
     * @param array $keys Tableau de clés à supprimer
     * @return bool True si toutes les opérations réussissent
     */
    public function deleteMultiple(array $keys): bool;

    /**
     * Incrémente une valeur numérique
     *
     * @param string $key La clé à incrémenter
     * @param int $value Valeur d'incrémentation (défaut: 1)
     * @return int|false La nouvelle valeur ou false si échec
     */
    public function increment(string $key, int $value = 1): int|false;

    /**
     * Décrémente une valeur numérique
     *
     * @param string $key La clé à décrémenter
     * @param int $value Valeur de décrémentation (défaut: 1)
     * @return int|false La nouvelle valeur ou false si échec
     */
    public function decrement(string $key, int $value = 1): int|false;

    /**
     * Remember pattern: récupère ou exécute et stocke
     *
     * @param string $key La clé du cache
     * @param callable $callback Fonction à exécuter si la clé n'existe pas
     * @param int|null $ttl Durée de vie en secondes
     * @return mixed La valeur en cache ou le résultat du callback
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed;

    /**
     * Récupère les statistiques du driver
     *
     * @return array Tableau de statistiques (vary selon driver)
     */
    public function getStats(): array;
}
