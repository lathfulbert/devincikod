@extends('admin.layout')

@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3"><i data-feather="plus-circle"></i> <?= $title ?></h1>
                <a href="<?= url('/admin/i18n?locale=' . $locale) ?>" class="btn btn-secondary">
                    <i data-feather="arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Alert -->
    <?php component('alert') ?>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nouvelle Traduction</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/i18n/create') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="locale" value="<?= $locale ?>">

                        <div class="mb-3">
                            <label for="locale" class="form-label">Langue</label>
                            <input type="text"
                                class="form-control"
                                id="locale_display"
                                value="<?= strtoupper($locale) ?>"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label for="key" class="form-label">Clé de Traduction *</label>
                            <input type="text"
                                name="key"
                                id="key"
                                class="form-control <?= has_error('key') ? 'is-invalid' : '' ?>"
                                placeholder="ex: dashboard.welcome"
                                value="<?= escape(old('key')) ?>"
                                required>
                            <?php component('error', ['field' => 'key']) ?>
                            <small class="text-muted">
                                Format: <code>category.subcategory.key</code> (notation pointée)
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Traduction *</label>
                            <textarea name="value"
                                id="value"
                                class="form-control <?= has_error('value') ? 'is-invalid' : '' ?>"
                                rows="4"
                                placeholder="Entrez la traduction..."
                                required><?= escape(old('value')) ?></textarea>
                            <?php component('error', ['field' => 'value']) ?>
                            <small class="text-muted">
                                Variables: <code>:name</code> ou <code>{name}</code><br>
                                Pluriel: <code>texte singulier|texte pluriel</code>
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i data-feather="help-circle"></i> Exemples</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>Simple:</strong> <code>messages.saved</code> → <code>Enregistré avec succès</code></li>
                                        <li><strong>Avec variable:</strong> <code>auth.welcome</code> → <code>Bienvenue, :name</code></li>
                                        <li><strong>Pluriel:</strong> <code>items.count</code> → <code>:count élément|:count éléments</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i data-feather="info"></i>
                            <strong>Note:</strong> La traduction sera enregistrée comme override dans
                            <code>/storage/i18n/overrides/<?= $locale ?>.json</code>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= url('/admin/i18n?locale=' . $locale) ?>" class="btn btn-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="plus-circle"></i> Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Auto-resize textarea
    const textarea = document.getElementById('value');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Preview translation key usage
    const keyInput = document.getElementById('key');
    if (keyInput) {
        keyInput.addEventListener('input', function() {
            console.log('Usage: __t(\'' + this.value + '\')');
        });
    }
</script>
@endsection