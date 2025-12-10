@extends('backend.layouts.master')

@section('title', 'Modifier Champ Personnalisé')

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Contacts', 'url' => '/admin/contacts'],
        ['label' => 'Champs Personnalisés', 'url' => '/admin/contacts/fields'],
        ['label' => 'Modifier']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <form action="<?= url('/admin/contacts/fields/' . $field->id . '/update') ?>" method="POST" id="fieldForm">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-lg-8">
                <?php
                component('card-start', ['card_title' => "Informations du champ"]);
                ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nom du champ <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="<?= htmlspecialchars($field->name) ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control"
                        value="<?= htmlspecialchars($field->slug) ?>" readonly>
                    <small class="form-text text-muted">Le slug ne peut pas être modifié</small>
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="text" <?= $field->type === 'text' ? 'selected' : '' ?>>Texte</option>
                        <option value="textarea" <?= $field->type === 'textarea' ? 'selected' : '' ?>>Zone de texte</option>
                        <option value="number" <?= $field->type === 'number' ? 'selected' : '' ?>>Nombre</option>
                        <option value="date" <?= $field->type === 'date' ? 'selected' : '' ?>>Date</option>
                        <option value="select" <?= $field->type === 'select' ? 'selected' : '' ?>>Liste déroulante</option>
                    </select>
                </div>

                <div class="mb-3" id="optionsGroup" style="display: <?= $field->type === 'select' ? 'block' : 'none' ?>;">
                    <label for="options" class="form-label">Options (pour liste déroulante)</label>
                    <input type="text" name="options" id="options" class="form-control"
                        value="<?= $field->options ? implode(', ', $field->getOptions()) : '' ?>">
                    <small class="form-text text-muted">Séparez les options par des virgules</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="placeholder" class="form-label">Placeholder</label>
                            <input type="text" name="placeholder" id="placeholder" class="form-control"
                                value="<?= htmlspecialchars($field->placeholder ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="default_value" class="form-label">Valeur par défaut</label>
                            <input type="text" name="default_value" id="default_value" class="form-control"
                                value="<?= htmlspecialchars($field->default_value ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="help_text" class="form-label">Texte d'aide</label>
                    <textarea name="help_text" id="help_text" class="form-control" rows="2"><?= htmlspecialchars($field->help_text ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_required" id="is_required"
                            class="form-check-input" value="1" <?= $field->is_required ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_required">
                            Champ requis
                        </label>
                    </div>
                </div>

                <?php component('card-end'); ?>
            </div>

            <div class="col-lg-4">
                <?php
                component('card-start', ['card_title' => "Actions"]);
                ?>

                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i data-feather="save"></i> Mettre à jour
                </button>

                <a href="<?= url('/admin/contacts/fields') ?>" class="btn btn-secondary w-100">
                    <i data-feather="x"></i> Annuler
                </a>

                <?php component('card-end'); ?>

                <?php
                component('card-start', ['card_title' => "Informations"]);
                ?>

                <div class="alert alert-info mb-0">
                    <i data-feather="info"></i>
                    <p class="mb-1"><strong>Placeholder SMS:</strong></p>
                    <p class="mb-0"><code>custom.<?= htmlspecialchars($field->slug) ?></code></p>
                </div>

                <?php component('card-end'); ?>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Show/hide options field based on type
    const typeSelect = document.getElementById('type');
    const optionsGroup = document.getElementById('optionsGroup');

    typeSelect.addEventListener('change', function() {
        if (this.value === 'select') {
            optionsGroup.style.display = 'block';
        } else {
            optionsGroup.style.display = 'none';
        }
    });
</script>
@endsection