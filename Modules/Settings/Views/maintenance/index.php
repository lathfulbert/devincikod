@extends('backend.layouts.master')

@section('title', 'Mode Maintenance')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/settings') ?>">Settings</a></li>
                    <li class="breadcrumb-item active">Maintenance</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Status Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="activity"></i> Statut Actuel</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($config->is_enabled): ?>
                        <div class="alert alert-warning">
                            <i data-feather="alert-triangle"></i>
                            <h4 class="mt-3">MODE MAINTENANCE ACTIF</h4>
                            <p>Le site est actuellement en maintenance</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i data-feather="check-circle"></i>
                            <h4 class="mt-3">SITE ACCESSIBLE</h4>
                            <p>Le mode maintenance est désactivé</p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('/admin/maintenance/toggle') ?>" class="mt-4">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn <?= $config->is_enabled ? 'btn-success' : 'btn-warning' ?> btn-lg">
                            <i data-feather="<?= $config->is_enabled ? 'check' : 'x' ?>"></i>
                            <?= $config->is_enabled ? 'DÉSACTIVER' : 'ACTIVER' ?> Maintenance
                        </button>
                    </form>

                    <?php if ($config->end_time): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Fin prévue: <?= date('d/m/Y H:i', strtotime($config->end_time)) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Configuration Card -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="settings"></i> Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/maintenance/update') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <ul class="nav nav-tabs" id="maintenanceTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#general">Général</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#appearance">Apparence</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#schedule">Planification</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#access">Accès</a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <!-- General Tab -->
                            <div class="tab-pane fade show active" id="general">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title"
                                           value="<?= htmlspecialchars($config->title ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="4"><?= htmlspecialchars($config->message ?? '') ?></textarea>
                                    <small class="text-muted">Message affiché aux visiteurs</small>
                                </div>

                                <div class="mb-3">
                                    <label for="retry_after" class="form-label">Retry-After (secondes)</label>
                                    <input type="number" class="form-control" id="retry_after" name="retry_after"
                                           value="<?= $config->retry_after ?? 3600 ?>" min="60">
                                    <small class="text-muted">Délai suggéré avant nouvelle tentative (défaut: 3600s = 1h)</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_countdown" name="show_countdown"
                                               value="1" <?= $config->show_countdown ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="show_countdown">
                                            Afficher le compte à rebours
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Appearance Tab -->
                            <div class="tab-pane fade" id="appearance">
                                <div class="mb-3">
                                    <label for="background_color" class="form-label">Couleur de fond</label>
                                    <input type="color" class="form-control form-control-color" id="background_color"
                                           name="background_color" value="<?= $config->background_color ?? '#4466f2' ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="background_image" class="form-label">Image de fond</label>

                                    <?php if ($config->background_image): ?>
                                        <div class="mb-2">
                                            <img src="<?= asset($config->background_image) ?>"
                                                 alt="Background" class="img-thumbnail" style="max-height: 200px;">
                                            <a href="<?= url('/admin/maintenance/remove-background') ?>"
                                               class="btn btn-sm btn-danger ms-2">
                                                <i data-feather="trash-2"></i> Supprimer
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <input type="file" class="form-control" id="background_image"
                                           name="background_image" accept="image/*">
                                    <small class="text-muted">Format recommandé: 1920x1080px</small>
                                </div>
                            </div>

                            <!-- Schedule Tab -->
                            <div class="tab-pane fade" id="schedule">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Début de maintenance</label>
                                    <input type="datetime-local" class="form-control" id="start_time" name="start_time"
                                           value="<?= $config->start_time ? date('Y-m-d\TH:i', strtotime($config->start_time)) : '' ?>">
                                    <small class="text-muted">Laisser vide pour activation immédiate</small>
                                </div>

                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Fin de maintenance</label>
                                    <input type="datetime-local" class="form-control" id="end_time" name="end_time"
                                           value="<?= $config->end_time ? date('Y-m-d\TH:i', strtotime($config->end_time)) : '' ?>">
                                    <small class="text-muted">Laisser vide pour maintenance indéfinie</small>
                                </div>

                                <div class="alert alert-info">
                                    <i data-feather="info"></i>
                                    <strong>Planification automatique:</strong> Si vous définissez une période, le mode maintenance
                                    s'activera et se désactivera automatiquement.
                                </div>
                            </div>

                            <!-- Access Tab -->
                            <div class="tab-pane fade" id="access">
                                <div class="mb-3">
                                    <label for="allowed_ips" class="form-label">Adresses IP autorisées</label>
                                    <textarea class="form-control" id="allowed_ips" name="allowed_ips" rows="4"><?php
                                        if ($config->allowed_ips && is_array($config->allowed_ips)) {
                                            echo implode("\n", $config->allowed_ips);
                                        }
                                    ?></textarea>
                                    <small class="text-muted">Une IP par ligne (ex: 192.168.1.1)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="allowed_roles" class="form-label">Rôles autorisés</label>
                                    <select class="form-control" id="allowed_roles" name="allowed_roles[]" multiple>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role->id ?>"
                                                <?= ($config->allowed_roles && in_array($role->id, $config->allowed_roles)) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($role->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Maintenir Ctrl/Cmd pour sélectionner plusieurs rôles</small>
                                </div>

                                <div class="mb-3">
                                    <label for="allowed_users" class="form-label">Utilisateurs autorisés</label>
                                    <select class="form-control" id="allowed_users" name="allowed_users[]" multiple>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user->id ?>"
                                                <?= ($config->allowed_users && in_array($user->id, $config->allowed_users)) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user->name ?? $user->email) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Utilisateurs spécifiques ayant accès pendant la maintenance</small>
                                </div>

                                <div class="alert alert-warning">
                                    <i data-feather="alert-triangle"></i>
                                    <strong>Important:</strong> Les utilisateurs correspondant à au moins un critère
                                    (IP, rôle ou utilisateur spécifique) pourront accéder au site.
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Enregistrer la configuration
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
    feather.replace();
</script>
@endsection
