<?php

namespace App\Core\Database\Traits;

use App\Core\Database\Database;
use Modules\Users\Models\User;

/**
 * Trait HasAuthor
 *
 * Gère automatiquement le tracking des auteurs pour les opérations CRUD :
 * - created_by : ID de l'utilisateur qui a créé l'enregistrement
 * - updated_by : ID de l'utilisateur qui a modifié l'enregistrement
 * - deleted_by : ID de l'utilisateur qui a supprimé l'enregistrement (soft delete)
 *
 * Usage :
 * 1. Ajouter le trait dans votre modèle : use HasAuthor;
 * 2. S'assurer que les colonnes created_by, updated_by, deleted_by existent dans la table
 * 3. L'ORM gérera automatiquement ces champs lors des opérations CRUD
 *
 * @package App\Core\Database\Traits
 */
trait HasAuthor
{
    /**
     * Récupère l'ID de l'utilisateur actuellement connecté
     *
     * @return int|null
     */
    protected function getCurrentUserId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    /**
     * Hook appelé automatiquement avant la création d'un enregistrement
     *
     * @return void
     */
    protected function beforeCreate(): void
    {
        $userId = $this->getCurrentUserId();

        if ($userId !== null) {
            // Définir created_by lors de la création
            if (!isset($this->attributes['created_by'])) {
                $this->attributes['created_by'] = $userId;
            }

            // Définir updated_by aussi lors de la création
            if (!isset($this->attributes['updated_by'])) {
                $this->attributes['updated_by'] = $userId;
            }
        }
    }

    /**
     * Hook appelé automatiquement avant la mise à jour d'un enregistrement
     *
     * @return void
     */
    protected function beforeUpdate(): void
    {
        $userId = $this->getCurrentUserId();

        if ($userId !== null) {
            // Toujours mettre à jour updated_by lors d'une modification
            $this->attributes['updated_by'] = $userId;
        }
    }

    /**
     * Hook appelé automatiquement avant la suppression d'un enregistrement (soft delete)
     *
     * @return void
     */
    protected function beforeDelete(): void
    {
        $userId = $this->getCurrentUserId();

        if ($userId !== null) {
            // Définir deleted_by lors d'une suppression (si c'est un soft delete)
            $usesSoftDeletes = in_array('App\Core\Database\Traits\SoftDeletes', class_uses($this));

            if ($usesSoftDeletes) {
                $this->attributes['deleted_by'] = $userId;
            }
        }
    }

    /**
     * Relation avec l'utilisateur qui a créé l'enregistrement
     *
     * @return \App\Core\Database\ORM\Relations\BelongsTo|null
     */
    public function creator()
    {
        if (isset($this->attributes['created_by'])) {
            return $this->belongsTo(User::class, 'created_by', 'id');
        }
        return null;
    }

    /**
     * Relation avec l'utilisateur qui a modifié l'enregistrement
     *
     * @return \App\Core\Database\ORM\Relations\BelongsTo|null
     */
    public function updater()
    {
        if (isset($this->attributes['updated_by'])) {
            return $this->belongsTo(User::class, 'updated_by', 'id');
        }
        return null;
    }

    /**
     * Relation avec l'utilisateur qui a supprimé l'enregistrement
     *
     * @return \App\Core\Database\ORM\Relations\BelongsTo|null
     */
    public function deleter()
    {
        if (isset($this->attributes['deleted_by'])) {
            return $this->belongsTo(User::class, 'deleted_by', 'id');
        }
        return null;
    }

    /**
     * Obtenir le nom complet du créateur
     *
     * @return string|null
     */
    public function getCreatorName(): ?string
    {
        if (!isset($this->attributes['created_by'])) {
            return null;
        }

        $creator = User::find($this->attributes['created_by']);

        if ($creator) {
            return trim(($creator->first_name ?? '') . ' ' . ($creator->last_name ?? '')) ?: $creator->username;
        }

        return 'Utilisateur #' . $this->attributes['created_by'];
    }

    /**
     * Obtenir le nom complet du modificateur
     *
     * @return string|null
     */
    public function getUpdaterName(): ?string
    {
        if (!isset($this->attributes['updated_by'])) {
            return null;
        }

        $updater = User::find($this->attributes['updated_by']);

        if ($updater) {
            return trim(($updater->first_name ?? '') . ' ' . ($updater->last_name ?? '')) ?: $updater->username;
        }

        return 'Utilisateur #' . $this->attributes['updated_by'];
    }

    /**
     * Obtenir le nom complet de celui qui a supprimé
     *
     * @return string|null
     */
    public function getDeleterName(): ?string
    {
        if (!isset($this->attributes['deleted_by'])) {
            return null;
        }

        $deleter = User::find($this->attributes['deleted_by']);

        if ($deleter) {
            return trim(($deleter->first_name ?? '') . ' ' . ($deleter->last_name ?? '')) ?: $deleter->username;
        }

        return 'Utilisateur #' . $this->attributes['deleted_by'];
    }

    /**
     * Vérifie si l'enregistrement a été créé par l'utilisateur donné
     *
     * @param int $userId
     * @return bool
     */
    public function isCreatedBy(int $userId): bool
    {
        return isset($this->attributes['created_by']) && $this->attributes['created_by'] == $userId;
    }

    /**
     * Vérifie si l'enregistrement a été modifié par l'utilisateur donné
     *
     * @param int $userId
     * @return bool
     */
    public function isUpdatedBy(int $userId): bool
    {
        return isset($this->attributes['updated_by']) && $this->attributes['updated_by'] == $userId;
    }

    /**
     * Vérifie si l'enregistrement a été supprimé par l'utilisateur donné
     *
     * @param int $userId
     * @return bool
     */
    public function isDeletedBy(int $userId): bool
    {
        return isset($this->attributes['deleted_by']) && $this->attributes['deleted_by'] == $userId;
    }
}
