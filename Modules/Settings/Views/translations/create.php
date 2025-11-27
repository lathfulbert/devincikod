@extends('backend.layouts.master')

@section('title', 'Nouvelle Traduction')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Nouvelle Traduction</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">
                        <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Paramètres</li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/settings/translations') ?>">Traductions</a></li>
                    <li class="breadcrumb-item active">Nouvelle</li>
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
                    <h5>Ajouter une Traduction</h5>
                    <span>Créez une nouvelle clé de traduction pour votre application</span>
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

                    <form action="<?= url('admin/settings/translations/store') ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="key" class="form-label">Clé de traduction <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="key" name="key" required
                                       placeholder="ex: auth.login">
                                <small class="form-text text-muted">
                                    Utilisez la notation point pour les clés imbriquées (ex: module.section.key)
                                </small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="language" class="form-label">Langue <span class="text-danger">*</span></label>
                                <select class="form-control" id="language" name="language" required>
                                    <?php foreach ($languages as $code => $name): ?>
                                        <option value="<?= $code ?>" <?= $code === 'fr' ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="module" class="form-label">Module</label>
                                <select class="form-control" id="module" name="module">
                                    <?php foreach ($modules as $module): ?>
                                        <option value="<?= $module ?>" <?= $module === 'general' ? 'selected' : '' ?>>
                                            <?= ucfirst($module) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="value" class="form-label">Valeur <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="value" name="value" rows="4" required
                                          placeholder="Entrez la traduction..."></textarea>
                                <small class="form-text text-muted">
                                    Utilisez :param pour les paramètres dynamiques (ex: Bonjour :name)
                                    <br>
                                    Pour la pluralisation, utilisez | (ex: :count item|:count items)
                                </small>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <a href="<?= url('admin/settings/translations') ?>" class="btn btn-light">
                                <i data-feather="x"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Aide et exemples -->
            <div class="card mt-3">
                <div class="card-header pb-0">
                    <h5>Aide et Exemples</h5>
                </div>
                <div class="card-body">
                    <h6>Format des clés</h6>
                    <ul>
                        <li><code>auth.login</code> - Clé simple dans le module auth</li>
                        <li><code>users.messages.success</code> - Clé imbriquée</li>
                        <li><code>app.name</code> - Nom de l'application</li>
                    </ul>

                    <h6 class="mt-3">Paramètres dynamiques</h6>
                    <p>Utilisez <code>:param</code> pour insérer des valeurs dynamiques :</p>
                    <ul>
                        <li>Clé: <code>auth.welcome</code></li>
                        <li>Valeur: <code>Bienvenue, :name</code></li>
                        <li>Utilisation: <code>trans('auth.welcome', ['name' => 'John'])</code></li>
                        <li>Résultat: "Bienvenue, John"</li>
                    </ul>

                    <h6 class="mt-3">Pluralisation</h6>
                    <p>Utilisez <code>|</code> pour séparer singulier et pluriel :</p>
                    <ul>
                        <li>Clé: <code>users.count</code></li>
                        <li>Valeur: <code>:count utilisateur|:count utilisateurs</code></li>
                        <li>Utilisation: <code>trans_choice('users.count', 5, ['count' => 5])</code></li>
                        <li>Résultat: "5 utilisateurs"</li>
                    </ul>
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
