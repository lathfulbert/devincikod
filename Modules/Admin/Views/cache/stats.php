@extends('backend.layouts.master')

@section('title', $title ?? 'Statistiques du Cache')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Statistiques du Cache' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/cache') ?>">Cache</a></li>
                    <li class="breadcrumb-item active">Statistiques</li>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Statistiques du Cache</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <form method="POST" action="<?= url('/admin/cache/clear') ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir vider tout le cache ?');" style="display:inline;">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i data-feather="trash-2"></i> Vider le cache
                                </button>
                            </form>
                            <a href="<?= url('/admin/cache') ?>" class="btn btn-secondary btn-sm">
                                <i data-feather="settings"></i> Configuration
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded text-center">
                                <h6 class="text-muted mb-2">Driver Actif</h6>
                                <h3 class="mb-0"><?= ucfirst($stats['driver'] ?? 'N/A') ?></h3>
                            </div>
                        </div>

                        <?php if (isset($stats['total_files'])): ?>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <h6 class="text-muted mb-2">Fichiers</h6>
                                    <h3 class="mb-0"><?= $stats['total_files'] ?></h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <h6 class="text-muted mb-2">Taille</h6>
                                    <h3 class="mb-0"><?= $stats['total_size'] ?></h3>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($stats['keys_count'])): ?>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <h6 class="text-muted mb-2">Clés</h6>
                                    <h3 class="mb-0"><?= $stats['keys_count'] ?></h3>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($stats['memory_usage'])): ?>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <h6 class="text-muted mb-2">Mémoire</h6>
                                    <h3 class="mb-0"><?= $stats['memory_usage'] ?></h3>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($stats['hit_rate'])): ?>
                            <div class="col-md-3">
                                <div class="bg-light p-3 rounded text-center">
                                    <h6 class="text-muted mb-2">Hit Rate</h6>
                                    <h3 class="mb-0"><?= $stats['hit_rate'] ?></h3>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Métrique</th>
                                    <th>Valeur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats as $key => $value): ?>
                                    <?php if ($key === 'driver') continue; ?>
                                    <tr>
                                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></td>
                                        <td><?= htmlspecialchars($value) ?></td>
                                    </tr>
                                <?php endforeach; ?>
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
    feather.replace();
</script>
@endsection