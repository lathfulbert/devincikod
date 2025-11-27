@extends('backend.layouts.master')

@section('title', 'Modifier Traduction')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Modifier Traduction</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">
                        <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Paramètres</li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/settings/translations') ?>">Traductions</a></li>
                    <li class="breadcrumb-item active">Modifier</li>
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
                    <h5>Modifier la Traduction</h5>
                    <span>Mettez à jour la traduction existante</span>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['flash']['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i data-feather="alert-circle"></i>
                            <?= $_SESSION['flash']['error'] ?>
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['flash']['error']); ?>
                    <?php endif; ?>

                    <form action="<?= url('admin/settings/translations/' . $translation['id'] . '/update') ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="key" class="form-label">Clé de traduction</label>
                                <input type="text" class="form-control" id="key" name="key"
                                       value="<?= htmlspecialchars($translation['key']) ?>" disabled>
                                <small class="form-text text-muted">La clé ne peut pas être modifiée</small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="language" class="form-label">Langue</label>
                                <input type="text" class="form-control" id="language" name="language"
                                       value="<?= htmlspecialchars($translation['language']) ?>" disabled>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="module" class="form-label">Module</label>
                                <select class="form-control" id="module" name="module">
                                    <?php foreach ($modules as $module): ?>
                                        <option value="<?= $module ?>" <?= $module === $translation['module'] ? 'selected' : '' ?>>
                                            <?= ucfirst($module) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="value" class="form-label">Valeur <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="value" name="value" rows="6" required><?= htmlspecialchars($translation['value']) ?></textarea>
                                <small class="form-text text-muted">
                                    Utilisez :param pour les paramètres dynamiques | Pour la pluralisation, utilisez | (singulier|pluriel)
                                </small>
                            </div>

                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i data-feather="info"></i>
                                    <strong>Information:</strong> Les modifications seront enregistrées dans l'historique.
                                    Créé le: <?= htmlspecialchars($translation['created_at']) ?> |
                                    Dernière modification: <?= htmlspecialchars($translation['updated_at']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <a href="<?= url('admin/settings/translations') ?>" class="btn btn-light">
                                <i data-feather="x"></i> Annuler
                            </a>
                            <a href="<?= url('admin/settings/translations/' . $translation['id'] . '/history') ?>" class="btn btn-info">
                                <i data-feather="clock"></i> Voir l'historique
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Mettre à jour
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
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endsection
