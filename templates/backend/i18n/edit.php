@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3"><i data-feather="edit"></i> <?= $title ?></h1>
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
                    <h5 class="mb-0">Modifier la Traduction</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/i18n/edit') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="key" value="<?= escape($key) ?>">
                        <input type="hidden" name="locale" value="<?= $locale ?>">

                        <div class="mb-3">
                            <label for="key" class="form-label">Clé de Traduction</label>
                            <input type="text"
                                class="form-control"
                                id="key"
                                value="<?= escape($key) ?>"
                                readonly>
                            <small class="text-muted">
                                Utilisation: <code>&lt;?= __t('<?= escape($key) ?>') ?&gt;</code>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="locale" class="form-label">Langue</label>
                            <input type="text"
                                class="form-control"
                                id="locale"
                                value="<?= strtoupper($locale) ?>"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Traduction</label>
                            <textarea name="value"
                                id="value"
                                class="form-control <?= has_error('value') ? 'is-invalid' : '' ?>"
                                rows="4"
                                required><?= escape($value) ?></textarea>
                            <?php component('error', ['field' => 'value']) ?>
                            <small class="text-muted">
                                Variables: <code>:name</code> ou <code>{name}</code><br>
                                Pluriel: <code>texte singulier|texte pluriel</code>
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i data-feather="info"></i>
                            <strong>Note:</strong> Cette modification créera un override dans
                            <code>/storage/i18n/overrides/<?= $locale ?>.json</code>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= url('/admin/i18n?locale=' . $locale) ?>" class="btn btn-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Enregistrer
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
</script>
@endsection