@extends('backend.layouts.master')

@section('title', 'Gestion des Traductions')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Traductions</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">
                        <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Paramètres</li>
                    <li class="breadcrumb-item active">Traductions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Liste des Traductions</h5>
                            <span>Gérez les traductions multilingues de votre application</span>
                        </div>
                        <div>
                            <a href="<?= url('admin/settings/translations/create') ?>" class="btn btn-primary">
                                <i data-feather="plus"></i> Nouvelle Traduction
                            </a>
                            <a href="<?= url('admin/settings/translations/export') ?>" class="btn btn-success">
                                <i data-feather="download"></i> Exporter
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="languageFilter">
                                <option value="">Toutes les langues</option>
                                <option value="fr">Français</option>
                                <option value="en">English</option>
                                <option value="es">Español</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="moduleFilter">
                                <option value="">Tous les modules</option>
                                <option value="general">Général</option>
                                <option value="auth">Authentification</option>
                                <option value="admin">Administration</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="searchKey" placeholder="Rechercher une clé...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="translationsTable">
                            <thead>
                                <tr>
                                    <th>Clé</th>
                                    <th>Langue</th>
                                    <th>Valeur</th>
                                    <th>Module</th>
                                    <th>Date de MAJ</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($translations)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="py-4">
                                            <i data-feather="inbox" style="width: 48px; height: 48px;"></i>
                                            <p class="mt-2">Aucune traduction trouvée</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($translations as $translation): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($translation->key) ?></code></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($translation->language) ?></span></td>
                                        <td><?= htmlspecialchars(substr($translation->value, 0, 50)) ?><?= strlen($translation->value) > 50 ? '...' : '' ?></td>
                                        <td><?= htmlspecialchars($translation->module) ?></td>
                                        <td><?= htmlspecialchars($translation->updated_at) ?></td>
                                        <td>
                                            <a href="<?= url('admin/settings/translations/' . $translation->id . '/edit') ?>"
                                               class="btn btn-sm btn-warning" title="Modifier">
                                                <i data-feather="edit-2"></i>
                                            </a>
                                            <a href="<?= url('admin/settings/translations/' . $translation->id . '/history') ?>"
                                               class="btn btn-sm btn-info" title="Historique">
                                                <i data-feather="clock"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-translation"
                                                    data-id="<?= $translation->id ?>" title="Supprimer">
                                                <i data-feather="trash-2"></i>
                                            </button>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Delete translation
    document.querySelectorAll('.delete-translation').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette traduction ?')) {
                const id = this.dataset.id;
                fetch('<?= url('admin/settings/translations') ?>/' + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                }).then(() => location.reload());
            }
        });
    });
</script>
@endsection
