@extends('backend.layouts.master')

@section('title', $title ?? 'Backups')

@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Gestion des Backups</h1>
        </div>
        <div class="col-md-6 text-end">
            <form action="<?= route('admin.backups.create') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="database">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-database"></i> Backup DB
                </button>
            </form>
            <form action="<?= route('admin.backups.create') ?>" method="POST" class="d-inline ms-2">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="files">
                <button type="submit" class="btn btn-info text-white">
                    <i class="fas fa-file-archive"></i> Backup Fichiers
                </button>
            </form>
            <form action="<?= route('admin.backups.create') ?>" method="POST" class="d-inline ms-2">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="full">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-archive"></i> Backup Complet
                </button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Historique des sauvegardes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Fichier</th>
                            <th>Taille</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><?= $backup->id ?></td>
                                <td>
                                    <?php if ($backup->type == 'database'): ?>
                                        <span class="badge bg-primary">Database</span>
                                    <?php elseif ($backup->type == 'files'): ?>
                                        <span class="badge bg-info">Fichiers</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Complet</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $backup->filename ?></td>
                                <td><?= \Modules\Backup\Services\MonitoringService::formatBytes($backup->size) ?></td>
                                <td>
                                    <?php if ($backup->status == 'completed'): ?>
                                        <span class="badge bg-success">Succès</span>
                                    <?php elseif ($backup->status == 'failed'): ?>
                                        <span class="badge bg-danger">Échec</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">En cours</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $backup->created_at ?></td>
                                <td>
                                    <?php if ($backup->status == 'completed'): ?>
                                        <a href="<?= route('admin.backups.download', ['id' => $backup->id]) ?>" class="btn btn-sm btn-primary" title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="<?= route('admin.backups.restore', ['id' => $backup->id]) ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir restaurer ce backup ? Cela écrasera les données actuelles !');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-warning" title="Restaurer">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="<?= route('admin.backups.delete', ['id' => $backup->id]) ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce backup ?');">
                                        <?= csrf_field() ?>
                                        <?= method_field('DELETE') ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection