<?php

/**
 * Template CRUD: Formulaire de Création/Édition
 * 
 * Ce template est générique pour create et edit.
 * 
 * Variables requises:
 * - $item: object|null - L'élément à éditer (null pour création)
 * - $module_name: string - Nom du module (ex: 'users')
 * - $module_title: string - Titre du module (ex: 'Utilisateurs')
 * - $fields: array - Définition des champs du formulaire
 */

$is_edit = isset($item) && $item;
$form_action = $is_edit
    ? url('/admin/' . ($module_name ?? 'module') . '/update/' . $item->id)
    : url('/admin/' . ($module_name ?? 'module') . '/store');
?>
@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => $module_title ?? 'Module', 'url' => '/admin/' . ($module_name ?? 'module')],
    ['label' => $is_edit ? 'Éditer' : 'Créer']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <?php
        $card_title = ($is_edit ? "Éditer " : "Créer un nouveau ") . ($module_title_singular ?? 'élément');
        component('card-start');
        ?>

        <form method="POST" action="<?= $form_action ?>" <?= isset($has_file_upload) && $has_file_upload ? 'enctype="multipart/form-data"' : '' ?>>
            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <?php foreach ($fields ?? [] as $field): ?>
                <div class="mb-3">
                    <label for="<?= $field['name'] ?>" class="form-label">
                        <?= htmlspecialchars($field['label']) ?>
                        <?php if (!empty($field['required'])): ?>
                            <span class="text-danger">*</span>
                        <?php endif; ?>
                    </label>

                    <?php
                    $field_value = $is_edit ? ($item->{$field['name']} ?? '') : ($field['default'] ?? '');
                    $field_type = $field['type'] ?? 'text';

                    switch ($field_type):
                        case 'textarea':
                    ?>
                            <textarea name="<?= $field['name'] ?>"
                                id="<?= $field['name'] ?>"
                                class="form-control"
                                rows="<?= $field['rows'] ?? 4 ?>"
                                <?= !empty($field['required']) ? 'required' : '' ?>><?= htmlspecialchars($field_value) ?></textarea>
                        <?php
                            break;

                        case 'select':
                        ?>
                            <select name="<?= $field['name'] ?>"
                                id="<?= $field['name'] ?>"
                                class="form-select"
                                <?= !empty($field['required']) ? 'required' : '' ?>>
                                <option value="">-- Sélectionnez --</option>
                                <?php foreach ($field['options'] ?? [] as $option_value => $option_label): ?>
                                    <option value="<?= $option_value ?>" <?= $field_value == $option_value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($option_label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php
                            break;

                        case 'checkbox':
                        ?>
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                    name="<?= $field['name'] ?>"
                                    id="<?= $field['name'] ?>"
                                    class="form-check-input"
                                    value="1"
                                    <?= $field_value ? 'checked' : '' ?>>
                                <label class="form-check-label" for="<?= $field['name'] ?>">
                                    <?= $field['checkbox_label'] ?? 'Activer' ?>
                                </label>
                            </div>
                        <?php
                            break;

                        case 'file':
                        ?>
                            <input type="file"
                                name="<?= $field['name'] ?>"
                                id="<?= $field['name'] ?>"
                                class="form-control"
                                accept="<?= $field['accept'] ?? '*' ?>"
                                <?= !empty($field['required']) && !$is_edit ? 'required' : '' ?>>
                            <?php if ($is_edit && $field_value): ?>
                                <small class="text-muted">Fichier actuel: <?= basename($field_value) ?></small>
                            <?php endif; ?>
                        <?php
                            break;

                        case 'password':
                        ?>
                            <input type="password"
                                name="<?= $field['name'] ?>"
                                id="<?= $field['name'] ?>"
                                class="form-control"
                                autocomplete="<?= $field['autocomplete'] ?? 'new-password' ?>"
                                <?= !empty($field['required']) && !$is_edit ? 'required' : '' ?>>
                            <?php if ($is_edit): ?>
                                <small class="text-muted">Laisser vide pour ne pas changer</small>
                            <?php endif; ?>
                        <?php
                            break;

                        default: // text, email, number, date, etc.
                        ?>
                            <input type="<?= $field_type ?>"
                                name="<?= $field['name'] ?>"
                                id="<?= $field['name'] ?>"
                                class="form-control"
                                value="<?= htmlspecialchars($field_value) ?>"
                                <?= !empty($field['required']) ? 'required' : '' ?>
                                <?= isset($field['min']) ? 'min="' . $field['min'] . '"' : '' ?>
                                <?= isset($field['max']) ? 'max="' . $field['max'] . '"' : '' ?>
                                <?= isset($field['step']) ? 'step="' . $field['step'] . '"' : '' ?>
                                <?= isset($field['pattern']) ? 'pattern="' . $field['pattern'] . '"' : '' ?>>
                    <?php
                    endswitch;
                    ?>

                    <?php if (!empty($field['help'])): ?>
                        <small class="form-text text-muted d-block"><?= $field['help'] ?></small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Boutons d'action -->
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save"></i> <?= $is_edit ? 'Mettre à jour' : 'Créer' ?>
                </button>
                <a href="<?= url('/admin/' . ($module_name ?? 'module')) ?>" class="btn btn-secondary">
                    <i data-feather="x"></i> Annuler
                </a>
                <?php if ($is_edit): ?>
                    <a href="<?= url('/admin/' . ($module_name ?? 'module') . '/delete/' . $item->id) ?>"
                        class="btn btn-danger float-end"
                        onclick="return confirm('Supprimer définitivement cet élément ?')">
                        <i data-feather="trash-2"></i> Supprimer
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <?php component('card-end'); ?>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validation personnalisée si nécessaire
        const form = document.querySelector('form');

        form.addEventListener('submit', function(e) {
            // Vous pouvez ajouter ici une validation personnalisée
        });
    });
</script>
@endsection