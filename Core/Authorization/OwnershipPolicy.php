<?php

namespace App\Core\Authorization;

/**
 * OwnershipPolicy - Base Policy for Ownership-based Authorization
 *
 * Permet de gérer les autorisations basées sur la propriété des données.
 * Les admins ont accès à tout, les autres utilisateurs uniquement à ce qu'ils ont créé.
 */
class OwnershipPolicy
{
    /**
     * Vérifie si l'utilisateur est admin
     *
     * @param mixed $user
     * @return bool
     */
    protected function isAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        // Vérifier le rôle admin
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('super_admin');
        }

        // Vérifier via les rôles chargés
        if (isset($user->roles)) {
            foreach ($user->roles as $role) {
                if (in_array($role->slug ?? $role['slug'] ?? '', ['admin', 'super_admin'])) {
                    return true;
                }
            }
        }

        // Vérifier via la session
        if (isset($_SESSION['user']['roles'])) {
            foreach ($_SESSION['user']['roles'] as $role) {
                if (in_array($role['slug'] ?? '', ['admin', 'super_admin'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur est le propriétaire de la ressource
     *
     * @param mixed $user
     * @param mixed $resource
     * @param string $ownerField Le champ qui contient l'ID du propriétaire
     * @return bool
     */
    protected function isOwner($user, $resource, string $ownerField = 'user_id'): bool
    {
        if (!$user || !$resource) {
            return false;
        }

        $userId = $user->id ?? $user['id'] ?? $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            return false;
        }

        // Si la ressource est un tableau
        if (is_array($resource)) {
            return isset($resource[$ownerField]) && $resource[$ownerField] == $userId;
        }

        // Si la ressource est un objet
        if (is_object($resource)) {
            // Via propriété publique
            if (isset($resource->{$ownerField})) {
                return $resource->{$ownerField} == $userId;
            }

            // Via attributs
            if (isset($resource->attributes[$ownerField])) {
                return $resource->attributes[$ownerField] == $userId;
            }

            // Via getter
            $getter = 'get' . ucfirst(str_replace('_', '', ucwords($ownerField, '_')));
            if (method_exists($resource, $getter)) {
                return $resource->{$getter}() == $userId;
            }
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur est le créateur de la ressource
     *
     * @param mixed $user
     * @param mixed $resource
     * @return bool
     */
    protected function isCreator($user, $resource): bool
    {
        return $this->isOwner($user, $resource, 'created_by');
    }

    /**
     * Vérifie si l'utilisateur peut voir la ressource
     * Admin : tout voir
     * Autres : uniquement ce qu'ils ont créé
     *
     * @param mixed $user
     * @param mixed $resource
     * @param string $ownerField
     * @return bool
     */
    public function view($user, $resource, string $ownerField = 'user_id'): bool
    {
        return $this->isAdmin($user) || $this->isOwner($user, $resource, $ownerField);
    }

    /**
     * Vérifie si l'utilisateur peut mettre à jour la ressource
     *
     * @param mixed $user
     * @param mixed $resource
     * @param string $ownerField
     * @return bool
     */
    public function update($user, $resource, string $ownerField = 'user_id'): bool
    {
        return $this->isAdmin($user) || $this->isOwner($user, $resource, $ownerField);
    }

    /**
     * Vérifie si l'utilisateur peut supprimer la ressource
     *
     * @param mixed $user
     * @param mixed $resource
     * @param string $ownerField
     * @return bool
     */
    public function delete($user, $resource, string $ownerField = 'user_id'): bool
    {
        return $this->isAdmin($user) || $this->isOwner($user, $resource, $ownerField);
    }

    /**
     * Vérifie si l'utilisateur peut créer une ressource
     * Par défaut, tous les utilisateurs authentifiés peuvent créer
     *
     * @param mixed $user
     * @return bool
     */
    public function create($user): bool
    {
        return $user !== null;
    }

    /**
     * Vérifie si l'utilisateur peut voir toutes les ressources
     * Seuls les admins peuvent voir tout
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAll($user): bool
    {
        return $this->isAdmin($user);
    }
}
