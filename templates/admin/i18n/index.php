@extends('admin.layout')

@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3"><i data-feather="globe"></i> <?= $title ?></h1>
                <div>
                    <a href="<?= url('/admin/i18n/create?locale=' . $current_locale) ?>" class="btn btn-primary">
                        <i data-feather="plus"></i> Nouvelle Traduction
                    </a>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i data-feather="upload"></i> Importer
                    </button>
                    <a href="<?= url('/admin/i18n/export?locale=' . $current_locale) ?>" class="btn btn-success">
                        <i data-feather="download"></i> Exporter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Language Selector & Cache Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i data-feather="flag"></i> Langue Active</h5>
                    <div class="btn-group" role="group">
                        <?php foreach ($supported_locales as $locale): ?>
                            <a href="<?= url('/admin/i18n?locale=' . $locale) ?>"
                                class="btn <?= $locale === $current_locale ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <?= strtoupper($locale) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i data-feather="database"></i> Cache</h5>
                    <p class="mb-2">
                        <strong>Locales en cache:</strong> <?= $cache_stats['total_cached'] ?? 0 ?>
                    </p>
                    <form method="POST" action="<?= url('/admin/i18n/clear-cache') ?>" style="display: inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="locale" value="<?= $current_locale ?>">
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i data-feather="trash-2"></i> Vider Cache (<?= strtoupper($current_locale) ?>)
                        </button>
                    </form>
                    <form method="POST" action="<?= url('/admin/i18n/clear-cache') ?>" style="display: inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i data-feather="trash"></i> Vider Tout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert -->
    <?php component('alert') ?>

    <!-- Translations Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Traductions (<?= strtoupper($current_locale) ?>)</h5>
                <input type="text" id="searchInput" class="form-control w-25" placeholder="Rechercher...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="translationsTable">
                    <thead>
                        <tr>
                            <th>Clé</th>
                            <th>Traduction</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($translations)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    <i data-feather="alert-circle"></i> Aucune traduction trouvée
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($translations as $key => $value): ?>
                                <tr>
                                    <td><code><?= escape($key) ?></code></td>
                                    <td><?= escape($value) ?></td>
                                    <td>
                                        <a href="<?= url('/admin/i18n/edit?key=' . urlencode($key) . '&locale=' . $current_locale) ?>"
                                            class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i data-feather="edit-2"></i>
                                        </a>
                                        <form method="POST" action="<?= url('/admin/i18n/delete') ?>"
                                            style="display: inline;"
                                            onsubmit="return confirm('Supprimer l\'override pour cette clé?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="key" value="<?= escape($key) ?>">
                                            <input type="hidden" name="locale" value="<?= $current_locale ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer override">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/admin/i18n/import') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i data-feather="upload"></i> Importer Traductions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importLocale" class="form-label">Langue</label>
                        <select name="locale" id="importLocale" class="form-select" required>
                            <?php foreach ($supported_locales as $locale): ?>
                                <option value="<?= $locale ?>" <?= $locale === $current_locale ? 'selected' : '' ?>>
                                    <?= strtoupper($locale) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Fichier JSON</label>
                        <input type="file" name="file" id="importFile" class="form-control" accept=".json" required>
                        <small class="text-muted">Format JSON uniquement</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="upload"></i> Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#translationsTable tbody tr');

        rows.forEach(row => {
            const key = row.cells[0]?.textContent.toLowerCase() || '';
            const value = row.cells[1]?.textContent.toLowerCase() || '';

            if (key.includes(searchTerm) || value.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endsection