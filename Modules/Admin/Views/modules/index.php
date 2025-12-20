@extends('backend.layouts.master')

@section('title', 'Gestion des Modules')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Gestion des Modules</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route('admin.dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item active">Modules</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Liste des Modules</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="<?= route('admin.modules.upload') ?>" class="btn btn-primary">
                            <i data-feather="upload"></i> Installer un nouveau module
                        </a>
                    </div>

                    <table id="modulesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Version</th>
                                <th>Description</th>
                                <th>Auteur</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($modules)): ?>
                                <?php foreach ($modules as $module): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($module['name']) ?></strong></td>
                                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($module['version']) ?></span></td>
                                        <td><?= htmlspecialchars($module['description']) ?: '<span class="text-muted">-</span>' ?></td>
                                        <td><?= htmlspecialchars($module['author']) ?: '<span class="text-muted">-</span>' ?></td>
                                        <td>
                                            <?php if ($module['is_installed']): ?>
                                                <?php if ($module['is_enabled']): ?>
                                                    <span class="badge bg-success"><i data-feather="check-circle" style="width: 12px; height: 12px;"></i> Actif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><i data-feather="pause-circle" style="width: 12px; height: 12px;"></i> Désactivé</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i data-feather="download" style="width: 12px; height: 12px;"></i> Non installé</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if (!$module['is_installed']): ?>
                                                <form action="<?= url('/admin/modules/install') ?>" method="POST" style="display:inline;">
                                                    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                    <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Installer">
                                                        <i data-feather="download" style="width: 14px; height: 14px;"></i> Installer
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <?php if ($module['is_enabled']): ?>
                                                    <form action="<?= url('/admin/modules/disable') ?>" method="POST" style="display:inline;">
                                                        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                        <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Désactiver">
                                                            <i data-feather="pause" style="width: 14px; height: 14px;"></i> Désactiver
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="<?= url('/admin/modules/enable') ?>" method="POST" style="display:inline;">
                                                        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                        <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Activer">
                                                            <i data-feather="play" style="width: 14px; height: 14px;"></i> Activer
                                                        </button>
                                                    </form>

                                                    <form action="<?= url('/admin/modules/uninstall') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir désinstaller ce module ? Cela supprimera ses données.');">
                                                        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                        <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Désinstaller">
                                                            <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i data-feather="inbox"></i> Aucun module trouvé
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    Total : <?= count($modules ?? []) ?> module(s)
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<?php
$datatable_id = 'modulesTable';
component('datatable-init');
?>
<script>
    feather.replace();
</script>
@endsection