@extends('backend.layouts.master')

@section('title', 'System Health Monitor')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'System Health Monitor' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/settings') ?>">Settings</a></li>
                    <li class="breadcrumb-item active">Health Monitor</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Global Status -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <?php
                            $globalStatus = $healthCheck['global_status'];
                            $statusIcon = match($globalStatus) {
                                'ok' => 'check-circle',
                                'warning' => 'alert-triangle',
                                'error' => 'x-circle',
                                default => 'help-circle'
                            };
                            $statusColor = match($globalStatus) {
                                'ok' => 'success',
                                'warning' => 'warning',
                                'error' => 'danger',
                                default => 'secondary'
                            };
                            $statusText = match($globalStatus) {
                                'ok' => 'Système opérationnel',
                                'warning' => 'Attention requise',
                                'error' => 'Problème détecté',
                                default => 'État inconnu'
                            };
                            ?>
                            <i data-feather="<?= $statusIcon ?>" class="text-<?= $statusColor ?>" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-1"><?= $statusText ?></h4>
                            <p class="text-muted mb-0">
                                Dernière vérification: <?= date('d/m/Y H:i:s', strtotime($healthCheck['checked_at'])) ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <button onclick="location.reload()" class="btn btn-primary">
                                <i data-feather="refresh-cw"></i> Actualiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Status -->
    <div class="row">
        <?php foreach ($healthCheck['services'] as $serviceName => $service): ?>
            <?php
            $statusBadge = match($service['status']) {
                'ok' => 'success',
                'warning' => 'warning',
                'error' => 'danger',
                default => 'secondary'
            };

            $serviceTitle = match($serviceName) {
                'cron_job' => 'Cron Jobs',
                'queue_worker' => 'Queue Worker',
                default => ucfirst($serviceName)
            };

            $serviceIcon = match($serviceName) {
                'cron_job' => 'clock',
                'queue_worker' => 'layers',
                default => 'server'
            };
            ?>

            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i data-feather="<?= $serviceIcon ?>" style="width: 20px; height: 20px;"></i>
                                <?= $serviceTitle ?>
                            </h5>
                            <span class="badge badge-<?= $statusBadge ?>">
                                <?= strtoupper($service['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="mb-2">
                                <strong>Statut:</strong>
                                <span class="text-<?= $statusBadge ?>"><?= htmlspecialchars($service['message']) ?></span>
                            </p>
                        </div>

                        <?php if ($service['last_run']): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i data-feather="clock" style="width: 14px; height: 14px;"></i>
                                    Dernière exécution:
                                </small>
                                <br>
                                <strong><?= date('d/m/Y H:i:s', strtotime($service['last_run'])) ?></strong>
                                <br>
                                <small class="text-muted">
                                    (il y a <?= $service['hours_since'] ?> heures)
                                </small>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">
                                    <i data-feather="activity" style="width: 14px; height: 14px;"></i>
                                    Exécutions aujourd'hui:
                                </small>
                                <br>
                                <strong><?= $service['runs_today'] ?> fois</strong>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i data-feather="alert-triangle" style="width: 16px; height: 16px;"></i>
                                Aucune exécution enregistrée
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- API Information -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>API de Monitoring</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Vous pouvez surveiller l'état du système depuis l'extérieur via ces endpoints :</p>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code><?= url('/api/health') ?></code></td>
                                    <td>Retourne l'état du système en JSON</td>
                                    <td>
                                        <a href="<?= url('/api/health') ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i data-feather="external-link"></i> Tester
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code><?= url('/api/health/badge') ?></code></td>
                                    <td>Badge SVG de statut (pour README)</td>
                                    <td>
                                        <img src="<?= url('/api/health/badge') ?>" alt="Health Badge">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong><i data-feather="info"></i> Astuce:</strong>
                        Vous pouvez utiliser un service comme <strong>UptimeRobot</strong> ou <strong>Pingdom</strong>
                        pour monitorer l'endpoint <code>/api/health</code> et recevoir des alertes par email/SMS
                        en cas de problème.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Setup Instructions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Configuration des Services</h5>
                </div>
                <div class="card-body">
                    <h6>Pour que le monitoring fonctionne, vos cron jobs doivent enregistrer un heartbeat :</h6>

                    <div class="mb-3">
                        <strong>1. Dans vos scripts cron PHP :</strong>
                        <pre class="bg-light p-3 rounded"><code>&lt;?php
// Au début de votre script cron
require_once __DIR__ . '/bootstrap.php';

use App\Core\Services\HeartbeatHelper;

// Enregistrer le heartbeat
HeartbeatHelper::ping('cron_job');

// ... votre code cron ici ...
</code></pre>
                    </div>

                    <div class="mb-3">
                        <strong>2. Ou wrapper votre code :</strong>
                        <pre class="bg-light p-3 rounded"><code>&lt;?php
use App\Core\Services\HeartbeatHelper;

HeartbeatHelper::runWithHeartbeat('cron_job', function() {
    // Votre code cron ici
    processSmsQueue();
});
</code></pre>
                    </div>

                    <div class="mb-3">
                        <strong>3. Pour les queue workers :</strong>
                        <pre class="bg-light p-3 rounded"><code>&lt;?php
// Dans votre worker de queue
use App\Core\Services\HeartbeatHelper;

while (true) {
    $job = getNextJob();

    if ($job) {
        HeartbeatHelper::ping('queue_worker');
        processJob($job);
    }

    sleep(5);
}
</code></pre>
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

    // Auto-refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection
