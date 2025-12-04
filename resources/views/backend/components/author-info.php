<?php
/**
 * Composant: Affichage des informations d'auteur
 *
 * Usage:
 *   <?php component('author-info', ['model' => $item]) ?>
 *   <?php component('author-info', ['model' => $item, 'show_updated' => true]) ?>
 *   <?php component('author-info', ['model' => $item, 'layout' => 'compact']) ?>
 *
 * Paramètres:
 *   @param Model  $model         - Le modèle avec HasAuthor trait
 *   @param bool   $show_updated  - Afficher aussi updated_by (défaut: true)
 *   @param bool   $show_deleted  - Afficher aussi deleted_by si soft deleted (défaut: false)
 *   @param string $layout        - 'default', 'compact', 'inline', ou 'detailed' (défaut: 'default')
 *   @param string $icon_class    - Classe CSS pour l'icône (défaut: 'fas fa-user-circle')
 */

// Valeurs par défaut
$model = $model ?? null;
$show_updated = $show_updated ?? true;
$show_deleted = $show_deleted ?? false;
$layout = $layout ?? 'default';
$icon_class = $icon_class ?? 'fas fa-user-circle';

if (!$model) {
    return;
}

// Vérifier que le modèle a le trait HasAuthor
if (!method_exists($model, 'getCreatorName')) {
    echo '<span class="text-muted">N/A</span>';
    return;
}

$creatorName = $model->getCreatorName();
$updaterName = $show_updated ? $model->getUpdaterName() : null;
$deleterName = $show_deleted && method_exists($model, 'trashed') && $model->trashed() ? $model->getDeleterName() : null;
?>

<?php if ($layout === 'compact'): ?>
    <!-- Layout Compact: Une seule ligne -->
    <span class="author-info-compact">
        <i class="<?= $icon_class ?> text-primary"></i>
        <?= e($creatorName ?? 'N/A') ?>
        <?php if ($updaterName && $updaterName !== $creatorName): ?>
            <small class="text-info ml-2" title="Modifié par">
                <i class="fas fa-edit"></i>
                <?= e($updaterName) ?>
            </small>
        <?php endif; ?>
    </span>

<?php elseif ($layout === 'inline'): ?>
    <!-- Layout Inline: Texte simple -->
    <span class="author-info-inline">
        <?= e($creatorName ?? 'N/A') ?>
    </span>

<?php elseif ($layout === 'detailed'): ?>
    <!-- Layout Detailed: Carte avec toutes les informations -->
    <div class="author-info-detailed card card-body p-3">
        <div class="mb-2">
            <strong><i class="fas fa-user-plus text-success"></i> Créé par:</strong>
            <span><?= e($creatorName ?? 'N/A') ?></span>
            <?php if (isset($model->created_at)): ?>
                <small class="text-muted d-block">
                    <?= date('d/m/Y à H:i', strtotime($model->created_at)) ?>
                </small>
            <?php endif; ?>
        </div>

        <?php if ($updaterName): ?>
        <div class="mb-2">
            <strong><i class="fas fa-user-edit text-info"></i> Modifié par:</strong>
            <span><?= e($updaterName) ?></span>
            <?php if (isset($model->updated_at) && $model->updated_at !== $model->created_at): ?>
                <small class="text-muted d-block">
                    <?= date('d/m/Y à H:i', strtotime($model->updated_at)) ?>
                </small>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($deleterName): ?>
        <div>
            <strong><i class="fas fa-user-times text-danger"></i> Supprimé par:</strong>
            <span><?= e($deleterName) ?></span>
            <?php if (isset($model->deleted_at)): ?>
                <small class="text-muted d-block">
                    <?= date('d/m/Y à H:i', strtotime($model->deleted_at)) ?>
                </small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- Layout Default: Avec avatar et informations -->
    <div class="author-info-default d-flex align-items-center">
        <div class="author-avatar mr-2">
            <i class="<?= $icon_class ?> text-primary" style="font-size: 1.5rem;"></i>
        </div>
        <div class="author-details">
            <div class="author-name">
                <strong><?= e($creatorName ?? 'N/A') ?></strong>
            </div>
            <?php if (isset($model->created_at)): ?>
                <small class="text-muted">
                    <?= date('d/m/Y H:i', strtotime($model->created_at)) ?>
                </small>
            <?php endif; ?>
            <?php if ($updaterName && $updaterName !== $creatorName): ?>
                <div class="mt-1">
                    <small class="text-info">
                        <i class="fas fa-edit"></i>
                        Modifié par <?= e($updaterName) ?>
                        <?php if (isset($model->updated_at)): ?>
                            (<?= date('d/m/Y H:i', strtotime($model->updated_at)) ?>)
                        <?php endif; ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<style>
.author-info-compact {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.author-info-inline {
    font-size: 0.9rem;
}

.author-info-default {
    min-height: 50px;
}

.author-info-detailed {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
}

.author-avatar {
    min-width: 30px;
}

.author-details {
    flex: 1;
}
</style>
