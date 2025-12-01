@extends('backend.layouts.master')

@section('title', 'Nouveau Champ Personnalisé')

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Contacts', 'url' => '/admin/contacts'],
        ['label' => 'Champs Personnalisés', 'url' => '/admin/contacts/fields'],
        ['label' => 'Créer']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <form action="<?= url('/admin/contacts/fields/store') ?>" method="POST" id="fieldForm">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-lg-8">
                <?php
                $card_title = "Informations du champ";
                component('card-start');
                ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nom du champ <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="<?= old('name') ?>" required autofocus>
                    <small class="form-text text-muted">Ex: "Entreprise", "Ville", "Date rendez-vous"</small>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control"
                        value="<?= old('slug') ?>" readonly>
                    <small class="form-text text-muted">Généré automatiquement. Utilisé comme: custom.slug</small>
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="text">Texte</option>
                        <option value="textarea">Zone de texte</option>
                        <option value="number">Nombre</option>
                        <option value="date">Date</option>
                        <option value="select">Liste déroulante</option>
                    </select>
                </div>

                <div class="mb-3" id="optionsGroup" style="display: none;">
                    <label for="options" class="form-label">Options (pour liste déroulante)</label>
                    <input type="text" name="options" id="options" class="form-control"
                        value="<?= old('options') ?>">
                    <small class="form-text text-muted">Séparez les options par des virgules. Ex: VIP, Standard, Nouveau</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="placeholder" class="form-label">Placeholder</label>
                            <input type="text" name="placeholder" id="placeholder" class="form-control"
                                value="<?= old('placeholder') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="default_value" class="form-label">Valeur par défaut</label>
                            <input type="text" name="default_value" id="default_value" class="form-control"
                                value="<?= old('default_value') ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="help_text" class="form-label">Texte d'aide</label>
                    <textarea name="help_text" id="help_text" class="form-control" rows="2"><?= old('help_text') ?></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_required" id="is_required"
                            class="form-check-input" value="1" <?= old('is_required') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_required">
                            Champ requis
                        </label>
                    </div>
                </div>

                <?php component('card-end'); ?>
            </div>

            <div class="col-lg-4">
                <?php
                $card_title = "Actions";
                component('card-start');
                ?>

                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i data-feather="save"></i> Créer le champ
                </button>

                <a href="<?= url('/admin/contacts/fields') ?>" class="btn btn-secondary w-100">
                    <i data-feather="x"></i> Annuler
                </a>

                <?php component('card-end'); ?>

                <?php
                $card_title = "Informations";
                component('card-start');
                ?>

                <div class="alert alert-info mb-0">
                    <i data-feather="info"></i>
                    <p class="mb-1"><strong>Utilisation:</strong></p>
                    <p class="mb-0">Ce champ pourra être utilisé dans vos messages SMS avec le placeholder: <code id="placeholder-preview">custom.slug</code></p>
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

    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const placeholderPreview = document.getElementById('placeholder-preview');

    nameInput.addEventListener('input', function() {
        const slug = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_|_$/g, '');

        slugInput.value = slug;
        placeholderPreview.textContent = 'custom.' + (slug || 'slug');
    });

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