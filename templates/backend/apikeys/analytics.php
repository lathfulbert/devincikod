@extends('backend.layouts.master')

@section('title', $title ?? 'API Analytics')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'API Keys', 'url' => '/admin/system-api-keys'],
        ['label' => 'Analytics']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<!-- Filters -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= url('/admin/system-api-keys/analytics') ?>" class="row g-3">
                        <div class="col-md-5">
                            <label for="api_key_id" class="form-label">Select API Key</label>
                            <select name="api_key_id" id="api_key_id" class="form-select" onchange="this.form.submit()">
                                <?php if (empty($apiKeys)): ?>
                                    <option value="">No API keys available</option>
                                <?php else: ?>
                                    <?php foreach ($apiKeys as $key): ?>
                                        <option value="<?= $key->id ?>" <?= $selectedKeyId == $key->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($key->name) ?> (<?= htmlspecialchars($key->prefix) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="period" class="form-label">Time Period</label>
                            <select name="period" id="period" class="form-select" onchange="this.form.submit()">
                                <option value="1h" <?= $period == '1h' ? 'selected' : '' ?>>Last Hour</option>
                                <option value="24h" <?= $period == '24h' ? 'selected' : '' ?>>Last 24 Hours</option>
                                <option value="7d" <?= $period == '7d' ? 'selected' : '' ?>>Last 7 Days</option>
                                <option value="30d" <?= $period == '30d' ? 'selected' : '' ?>>Last 30 Days</option>
                                <option value="90d" <?= $period == '90d' ? 'selected' : '' ?>>Last 90 Days</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <a href="<?= url('/admin/system-api-keys/analytics/export?api_key_id=' . $selectedKeyId . '&period=' . $period . '&format=csv') ?>"
                               class="btn btn-outline-primary">
                                <i data-feather="download"></i> Export CSV
                            </a>
                            <a href="<?= url('/admin/system-api-keys/analytics/export?api_key_id=' . $selectedKeyId . '&period=' . $period . '&format=json') ?>"
                               class="btn btn-outline-secondary">
                                <i data-feather="file-text"></i> JSON
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($selectedKeyId && $stats): ?>

<!-- Statistics Cards -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Requests</h6>
                            <h2 class="mt-2 mb-0"><?= number_format($stats['total_requests'] ?? 0) ?></h2>
                        </div>
                        <i data-feather="activity" style="width: 40px; height: 40px; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Success Rate</h6>
                            <h2 class="mt-2 mb-0">
                                <?php
                                $total = $stats['total_requests'] ?? 1;
                                $successRate = $total > 0 ? ($stats['success_count'] / $total) * 100 : 0;
                                echo number_format($successRate, 1) . '%';
                                ?>
                            </h2>
                        </div>
                        <i data-feather="check-circle" style="width: 40px; height: 40px; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Avg Response Time</h6>
                            <h2 class="mt-2 mb-0"><?= number_format($stats['avg_response_time'] ?? 0) ?> ms</h2>
                        </div>
                        <i data-feather="zap" style="width: 40px; height: 40px; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Errors</h6>
                            <h2 class="mt-2 mb-0"><?= number_format($stats['error_count'] ?? 0) ?></h2>
                        </div>
                        <i data-feather="alert-triangle" style="width: 40px; height: 40px; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-md-8">
            <?php
            $card_title = "Request Timeline";
            component('card-start');
            ?>
            <canvas id="timeSeriesChart" height="80"></canvas>
            <?php component('card-end'); ?>
        </div>

        <div class="col-md-4">
            <?php
            $card_title = "Status Code Distribution";
            component('card-start');
            ?>
            <canvas id="statusChart" height="160"></canvas>
            <?php component('card-end'); ?>
        </div>
    </div>
</div>

<!-- Endpoint Statistics -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-12">
            <?php
            $card_title = "Endpoint Statistics";
            component('card-start');
            ?>

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Requests</th>
                        <th>Avg Response Time</th>
                        <th>Success</th>
                        <th>Errors</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($endpointStats)): ?>
                        <?php foreach ($endpointStats as $endpoint): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($endpoint['endpoint']) ?></code></td>
                                <td><?= number_format($endpoint['request_count']) ?></td>
                                <td><?= number_format($endpoint['avg_response_time']) ?> ms</td>
                                <td><span class="badge bg-success"><?= number_format($endpoint['success_count']) ?></span></td>
                                <td><span class="badge bg-danger"><?= number_format($endpoint['error_count']) ?></span></td>
                                <td>
                                    <?php
                                    $rate = ($endpoint['request_count'] > 0)
                                        ? ($endpoint['success_count'] / $endpoint['request_count']) * 100
                                        : 0;
                                    $color = $rate >= 95 ? 'success' : ($rate >= 80 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= number_format($rate, 1) ?>%</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No data available for this period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

<!-- Recent Logs -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php
            $card_title = "Recent API Requests";
            component('card-start');
            ?>

            <table id="logsTable" class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Endpoint</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>IP</th>
                        <th>Response Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td><?= date('H:i:s', strtotime($log->requested_at)) ?></td>
                                <td><code><?= htmlspecialchars($log->endpoint) ?></code></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($log->method) ?></span></td>
                                <td>
                                    <?php
                                    $statusClass = $log->status_code >= 200 && $log->status_code < 300 ? 'success'
                                        : ($log->status_code >= 400 ? 'danger' : 'warning');
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $log->status_code ?></span>
                                </td>
                                <td><?= htmlspecialchars($log->ip_address) ?></td>
                                <td><?= number_format($log->response_time) ?> ms</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No recent requests</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

<?php else: ?>

<div class="container-fluid">
    <div class="alert alert-info">
        <i data-feather="info"></i>
        <strong>No data available.</strong> Generate an API key and start making requests to see analytics.
    </div>
</div>

<?php endif; ?>

@endsection

@section('scripts')
<?php
$datatable_id = 'logsTable';
component('datatable-init');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    feather.replace();

    <?php if ($selectedKeyId && !empty($timeSeries)): ?>
    // Time Series Chart
    const timeSeriesCtx = document.getElementById('timeSeriesChart').getContext('2d');
    const timeSeriesChart = new Chart(timeSeriesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($timeSeries, 'time_bucket')) ?>,
            datasets: [{
                label: 'Requests',
                data: <?= json_encode(array_column($timeSeries, 'request_count')) ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    <?php endif; ?>

    <?php if ($selectedKeyId && !empty($statusDistribution)): ?>
    // Status Code Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($statusDistribution, 'status_code')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($statusDistribution, 'count')) ?>,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>
</script>
@endsection
