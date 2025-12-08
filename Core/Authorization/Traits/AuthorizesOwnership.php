<?php

namespace App\Core\Authorization\Traits;

use App\Core\Authorization\OwnershipPolicy;

/**
 * Trait AuthorizesOwnership
 *
 * Fournit des méthodes pour autoriser les actions basées sur la propriété
 * Usage: use AuthorizesOwnership dans vos controllers
 */
trait AuthorizesOwnership
{
    /**
     * Instance de la policy
     *
     * @var OwnershipPolicy
     */
    protected OwnershipPolicy $policy;

    /**
     * Initialise la policy
     *
     * @return void
     */
    protected function initializeOwnershipPolicy(): void
    {
        if (!isset($this->policy)) {
            $this->policy = new OwnershipPolicy();
        }
    }

    /**
     * Récupère l'utilisateur connecté
     *
     * @return mixed
     */
    protected function currentUser()
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Vérifie si l'utilisateur actuel est admin
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        $this->initializeOwnershipPolicy();
        return $this->policy->viewAll($this->currentUser());
    }

    /**
     * Vérifie si l'utilisateur peut voir la ressource
     *
     * @param mixed $resource
     * @param string $ownerField
     * @return bool
     */
    protected function canView($resource, string $ownerField = 'user_id'): bool
    {
        $this->initializeOwnershipPolicy();
        return $this->policy->view($this->currentUser(), $resource, $ownerField);
    }

    /**
     * Vérifie si l'utilisateur peut mettre à jour la ressource
     *
     * @param mixed $resource
     * @param string $ownerField
     * @return bool
     */
    protected function canUpdate($resource, string $ownerField = 'user_id'): bool
    {
        $this->initializeOwnershipPolicy();
        return $this->policy->update($this->currentUser(), $resource, $ownerField);
    }

    /**
     * Vérifie si l'utilisateur peut supprimer la ressource
     *
     * @param mixed $resource
     * @param string $ownerField
     * @return bool
     */
    protected function canDelete($resource, string $ownerField = 'user_id'): bool
    {
        $this->initializeOwnershipPolicy();
        return $this->policy->delete($this->currentUser(), $resource, $ownerField);
    }

    /**
     * Autorise la visualisation ou redirige avec erreur
     *
     * @param mixed $resource
     * @param string $ownerField
     * @param string|null $redirectUrl
     * @return void
     */
    protected function authorizeView($resource, string $ownerField = 'user_id', ?string $redirectUrl = null): void
    {
        if (!$this->canView($resource, $ownerField)) {
            $_SESSION['flash_error'] = 'Vous n\'avez pas l\'autorisation de voir cette ressource.';
            redirect($redirectUrl ?? $_SERVER['HTTP_REFERER'] ?? '/admin/dashboard');
            exit;
        }
    }

    /**
     * Autorise la mise à jour ou redirige avec erreur
     *
     * @param mixed $resource
     * @param string $ownerField
     * @param string|null $redirectUrl
     * @return void
     */
    protected function authorizeUpdate($resource, string $ownerField = 'user_id', ?string $redirectUrl = null): void
    {
        if (!$this->canUpdate($resource, $ownerField)) {
            $_SESSION['flash_error'] = 'Vous n\'avez pas l\'autorisation de modifier cette ressource.';
            redirect($redirectUrl ?? $_SERVER['HTTP_REFERER'] ?? '/admin/dashboard');
            exit;
        }
    }

    /**
     * Autorise la suppression ou redirige avec erreur
     *
     * @param mixed $resource
     * @param string $ownerField
     * @param string|null $redirectUrl
     * @return void
     */
    protected function authorizeDelete($resource, string $ownerField = 'user_id', ?string $redirectUrl = null): void
    {
        if (!$this->canDelete($resource, $ownerField)) {
            $_SESSION['flash_error'] = 'Vous n\'avez pas l\'autorisation de supprimer cette ressource.';
            redirect($redirectUrl ?? $_SERVER['HTTP_REFERER'] ?? '/admin/dashboard');
            exit;
        }
    }

    /**
     * Filtre une collection pour ne retourner que les ressources accessibles
     * Admin: tout
     * Autres: seulement ce qu'ils ont créé
     *
     * @param array $collection
     * @param string $ownerField
     * @return array
     */
    protected function filterByOwnership(array $collection, string $ownerField = 'user_id'): array
    {
        // Si admin, retourner tout
        if ($this->isAdmin()) {
            return $collection;
        }

        // Sinon, filtrer par propriétaire
        $userId = $this->currentUser()['id'] ?? null;

        if (!$userId) {
            return [];
        }

        return array_filter($collection, function ($item) use ($ownerField, $userId) {
            if (is_array($item)) {
                return isset($item[$ownerField]) && $item[$ownerField] == $userId;
            }

            if (is_object($item)) {
                return isset($item->{$ownerField}) && $item->{$ownerField} == $userId;
            }

            return false;
        });
    }

    /**
     * Ajoute une clause WHERE pour filtrer par propriétaire dans une requête
     *
     * @param \App\Core\Database\QueryBuilder $query
     * @param string $ownerField
     * @return \App\Core\Database\QueryBuilder
     */
    protected function scopeByOwnership($query, string $ownerField = 'user_id')
    {
        // Si admin, ne rien filtrer
        if ($this->isAdmin()) {
            return $query;
        }

        // Sinon, filtrer par utilisateur connecté
        $userId = $this->currentUser()['id'] ?? null;

        if ($userId) {
            $query->where($ownerField, $userId);
        }

        return $query;
    }
}
