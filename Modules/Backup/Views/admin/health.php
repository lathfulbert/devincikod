<?php $this->layout('backend.layouts.master', ['title' => 'Santé du Système']); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Santé du Système</h1>

    <div class="row">
        <!-- Disk Usage -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Espace Disque</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $health['disk']['percent'] ?>%</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-<?= $health['disk']['status'] == 'critical' ? 'danger' : ($health['disk']['status'] == 'warning' ? 'warning' : 'success') ?>" role="progressbar" style="width: <?= $health['disk']['percent'] ?>%" aria-valuenow="<?= $health['disk']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Utilisé: <?= $health['disk']['used'] ?> / Total: <?= $health['disk']['total'] ?></small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CPU Usage -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Charge CPU</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $health['cpu']['status'] == 'critical' || $health['cpu']['status'] == 'warning' ? $health['cpu']['load'] . '%' : 'OK' ?></div>
                            <div class="text-xs text-<?= $health['cpu']['status'] == 'critical' ? 'danger' : ($health['cpu']['status'] == 'warning' ? 'warning' : 'success') ?>">
                                Statut: <?= strtoupper($health['cpu']['status']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-microchip fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memory Usage -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mémoire RAM</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $health['memory']['percent'] ?>%</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-<?= $health['memory']['status'] == 'critical' ? 'danger' : ($health['memory']['status'] == 'warning' ? 'warning' : 'success') ?>" role="progressbar" style="width: <?= $health['memory']['percent'] ?>%" aria-valuenow="<?= $health['memory']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Utilisé: <?= $health['memory']['used'] ?> / Total: <?= $health['memory']['total'] ?></small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-memory fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">État des Services</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($health['services'] as $service => $status): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-capitalize"><?= $service ?></span>
                            <?php if ($status == 'running'): ?>
                                <span class="badge bg-success">Running</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Down</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>