@extends('backend.layouts.master')

@section('title', 'Gestion des Langues')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Gestion des Langues</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">
                        <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Paramètres</li>
                    <li class="breadcrumb-item active">Langues</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle"></i>
            <?= $_SESSION['flash']['success'] ?>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash']['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle"></i>
            <?= $_SESSION['flash']['error'] ?>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash']['warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i data-feather="alert-triangle"></i>
            <?= $_SESSION['flash']['warning'] ?>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']['warning']); ?>
    <?php endif; ?>

    <!-- Informations sur les langues actives -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Langue par défaut</h6>
                            <h3 class="mt-2 mb-0"><?= strtoupper($default_locale) ?></h3>
                        </div>
                        <div class="avatar-sm">
                            <i data-feather="globe" style="width: 32px; height: 32px; color: #7366ff;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Langue de fallback</h6>
                            <h3 class="mt-2 mb-0"><?= strtoupper($fallback_locale) ?></h3>
                        </div>
                        <div class="avatar-sm">
                            <i data-feather="shield" style="width: 32px; height: 32px; color: #61d800;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Langues actives</h6>
                            <h3 class="mt-2 mb-0">
                                <?= count(array_filter($languages, fn($l) => $l['is_active'])) ?>
                            </h3>
                        </div>
                        <div class="avatar-sm">
                            <i data-feather="flag" style="width: 32px; height: 32px; color: #f73164;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des langues -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Langues disponibles</h5>
                    <span>Activez ou désactivez les langues pour votre application</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="60">Drapeau</th>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Nom natif</th>
                                    <th>Direction</th>
                                    <th>Statut</th>
                                    <th>Fichier</th>
                                    <th width="280">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($languages as $code => $lang): ?>
                                <tr>
                                    <td>
                                        <i class="<?= $lang['flag'] ?>" style="font-size: 24px;"></i>
                                    </td>
                                    <td>
                                        <code><?= strtoupper($code) ?></code>
                                    </td>
                                    <td><?= $lang['name'] ?></td>
                                    <td><?= $lang['native_name'] ?></td>
                                    <td>
                                        <?php if ($lang['direction'] === 'rtl'): ?>
                                            <span class="badge badge-info">RTL</span>
                                        <?php else: ?>
                                            <span class="badge badge-light">LTR</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($lang['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>

                                        <?php if ($lang['is_default']): ?>
                                            <span class="badge badge-primary">Par défaut</span>
                                        <?php endif; ?>

                                        <?php if ($lang['is_fallback']): ?>
                                            <span class="badge badge-warning">Fallback</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($lang['has_file']): ?>
                                            <span class="text-success">
                                                <i data-feather="check-circle"></i> Existe
                                            </span>
                                        <?php else: ?>
                                            <span class="text-danger">
                                                <i data-feather="x-circle"></i> Manquant
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if ($lang['is_active']): ?>
                                                <?php if (!$lang['is_default'] && !$lang['is_fallback']): ?>
                                                    <form method="POST" action="<?= url('admin/settings/languages/deactivate') ?>" style="display: inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="code" value="<?= $code ?>">
                                                        <button type="submit" class="btn btn-warning" title="Désactiver">
                                                            <i data-feather="toggle-left"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if (!$lang['is_default']): ?>
                                                    <form method="POST" action="<?= url('admin/settings/languages/set-default') ?>" style="display: inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="code" value="<?= $code ?>">
                                                        <button type="submit" class="btn btn-primary" title="Définir par défaut">
                                                            <i data-feather="star"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if (!$lang['is_fallback']): ?>
                                                    <form method="POST" action="<?= url('admin/settings/languages/set-fallback') ?>" style="display: inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="code" value="<?= $code ?>">
                                                        <button type="submit" class="btn btn-info" title="Définir comme fallback">
                                                            <i data-feather="shield"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <form method="POST" action="<?= url('admin/settings/languages/activate') ?>" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="code" value="<?= $code ?>">
                                                    <button type="submit" class="btn btn-success" title="Activer">
                                                        <i data-feather="toggle-right"></i> Activer
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (!$lang['has_file']): ?>
                                                <form method="POST" action="<?= url('admin/settings/languages/create-file') ?>" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="code" value="<?= $code ?>">
                                                    <button type="submit" class="btn btn-secondary" title="Créer le fichier">
                                                        <i data-feather="file-plus"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <a href="<?= url('admin/settings/translations?language=' . $code) ?>"
                                               class="btn btn-light" title="Gérer les traductions">
                                                <i data-feather="edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations et aide -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Informations</h5>
                </div>
                <div class="card-body">
                    <h6>Statuts des langues</h6>
                    <ul>
                        <li><strong>Active :</strong> La langue est disponible pour les utilisateurs</li>
                        <li><strong>Par défaut :</strong> Langue utilisée par défaut pour les nouveaux utilisateurs</li>
                        <li><strong>Fallback :</strong> Langue utilisée si une traduction n'existe pas dans la langue active</li>
                    </ul>

                    <h6 class="mt-3">Actions disponibles</h6>
                    <ul>
                        <li><strong>Activer :</strong> Rendre la langue disponible dans l'application</li>
                        <li><strong>Désactiver :</strong> Masquer la langue (impossible pour la langue par défaut et fallback)</li>
                        <li><strong>Définir par défaut :</strong> Utiliser cette langue comme langue principale</li>
                        <li><strong>Définir comme fallback :</strong> Utiliser cette langue quand une traduction est manquante</li>
                        <li><strong>Créer le fichier :</strong> Créer le fichier JSON de traduction avec un modèle de base</li>
                        <li><strong>Gérer les traductions :</strong> Modifier les traductions pour cette langue</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <i data-feather="info"></i>
                        <strong>Note :</strong> Les modifications sont enregistrées dans le fichier
                        <code>config/app.php</code>. Le menu langue du header affichera automatiquement
                        les langues actives.
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

    // Confirmation avant désactivation
    document.querySelectorAll('form[action*="deactivate"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir désactiver cette langue ?')) {
                e.preventDefault();
            }
        });
    });

    // Confirmation avant changement de langue par défaut
    document.querySelectorAll('form[action*="set-default"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir changer la langue par défaut ?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection
