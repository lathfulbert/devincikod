@extends('backend.layouts.master')

@section('title', $title ?? 'API Monitoring')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'API Keys', 'url' => '/admin/system-api-keys'],
        ['label' => 'Monitoring']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<!-- Alerts Section -->
<?php if (!empty($allAlerts)): ?>
    <div class="container-fluid mb-4">
        <div class="row">
            <div class="col-12">
                <?php
                $card_title = "<i data-feather=\"alert-triangle\"></i> Alerts Requiring Attention";
                component('card-start');
                ?>

                <?php foreach ($allAlerts as $keyId => $alertData): ?>
                    <div class="mb-3">
                        <h5><?= htmlspecialchars($alertData['key']->name) ?></h5>
                        <?php foreach ($alertData['alerts'] as $alert): ?>
                            <?php
                            $alertClass = match ($alert['severity'] ?? 'medium') {
                                'high', 'critical' => 'danger',
                                'warning', 'medium' => 'warning',
                                default => 'info'
                            };
                            ?>
                            <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show" role="alert">
                                <strong><?= htmlspecialchars($alert['type']) ?>:</strong>
                                <?= htmlspecialchars($alert['message']) ?>
                                <?php if (!empty($alert['issues'])): ?>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($alert['issues'] as $issue): ?>
                                            <li><?= htmlspecialchars($issue) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <?php component('card-end'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Health Status Overview -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-12">
            <?php
            $card_title = "API Keys Health Status";
            component('card-start');
            ?>

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>API Key</th>
                        <th>Status</th>
                        <th>Requests (1h)</th>
                        <th>Error Rate</th>
                        <th>Avg Response Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($apiKeys)): ?>
                        <?php foreach ($apiKeys as $key): ?>
                            <?php
                            $health = $healthStatuses[$key->id] ?? ['status' => 'unknown', 'error_rate' => 0, 'avg_response_time' => 0, 'total_requests' => 0];
                            $statusClass = match ($health['status']) {
                                'healthy' => 'success',
                                'warning' => 'warning',
                                'critical' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($key->name ?? 'Unnamed') ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($key->prefix ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= ucfirst($health['status']) ?>
                                    </span>
                                </td>
                                <td><?= number_format($health['total_requests']) ?></td>
                                <td>
                                    <?php
                                    $errorRateClass = $health['error_rate'] > 10 ? 'danger' : ($health['error_rate'] > 5 ? 'warning' : 'success');
                                    ?>
                                    <span class="badge bg-<?= $errorRateClass ?>">
                                        <?= number_format($health['error_rate'], 2) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $responseTimeClass = $health['avg_response_time'] > 1000 ? 'warning' : 'success';
                                    ?>
                                    <span class="badge bg-<?= $responseTimeClass ?>">
                                        <?= number_format($health['avg_response_time']) ?> ms
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('/admin/system-api-keys/monitoring/details?api_key_id=' . $key->id) ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        <i data-feather="eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No API keys found. <a href="<?= url('/admin/system-api-keys') ?>">Create one</a> to start monitoring.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

<!-- System Info -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total API Keys</h6>
                    <h2><?= count($apiKeys ?? []) ?></h2>
                    <p class="text-muted mb-0">
                        Active: <?= count(array_filter($apiKeys ?? [], fn($k) => $k->is_active)) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Keys with Alerts</h6>
                    <h2 class="text-warning"><?= count($allAlerts ?? []) ?></h2>
                    <p class="text-muted mb-0">Requiring attention</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Healthy Keys</h6>
                    <?php
                    $healthyCount = count(array_filter($healthStatuses ?? [], fn($h) => $h['status'] === 'healthy'));
                    ?>
                    <h2 class="text-success"><?= $healthyCount ?></h2>
                    <p class="text-muted mb-0">Operating normally</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Auto-refresh health status every 30 seconds
    setInterval(() => {
        location.reload();
    }, 30000);
</script>
@endsection