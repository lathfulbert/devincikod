@extends('backend.layouts.master')

@section('title', 'Email Marketing Analytics')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Email Marketing Analytics' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <!-- Period Filter -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <a href="<?= url('/admin/email-marketing/analytics?period=day') ?>"
                   class="btn btn-<?= ($period ?? 'day') === 'day' ? 'primary' : 'outline-primary' ?>">
                    Today
                </a>
                <a href="<?= url('/admin/email-marketing/analytics?period=week') ?>"
                   class="btn btn-<?= ($period ?? '') === 'week' ? 'primary' : 'outline-primary' ?>">
                    This Week
                </a>
                <a href="<?= url('/admin/email-marketing/analytics?period=month') ?>"
                   class="btn btn-<?= ($period ?? '') === 'month' ? 'primary' : 'outline-primary' ?>">
                    This Month
                </a>
                <a href="<?= url('/admin/email-marketing/analytics?period=year') ?>"
                   class="btn btn-<?= ($period ?? '') === 'year' ? 'primary' : 'outline-primary' ?>">
                    This Year
                </a>
            </div>
        </div>
    </div>

    <!-- Global Statistics -->
    <div class="row">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="mb-0"><?= number_format($stats['total_emails']) ?></h3>
                            <p class="text-muted mb-0 small">Total Emails</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="font-primary" data-feather="mail" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="mb-0"><?= number_format($stats['sent']) ?></h3>
                            <p class="text-muted mb-0 small">Sent</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="font-success" data-feather="send" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="mb-0"><?= number_format($stats['delivered']) ?></h3>
                            <p class="text-muted mb-0 small">Delivered</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="font-info" data-feather="check-circle" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="mb-0"><?= number_format($stats['opened']) ?></h3>
                            <p class="text-muted mb-0 small">Opened (<?= $stats['open_rate'] ?>%)</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="font-warning" data-feather="eye" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="mb-0"><?= number_format($stats['clicked']) ?></h3>
                            <p class="text-muted mb-0 small">Clicked (<?= $stats['click_rate'] ?>%)</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="font-primary" data-feather="mouse-pointer" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="mb-0"><?= number_format($stats['bounced']) ?></h3>
                            <p class="text-muted mb-0 small">Bounced (<?= $stats['bounce_rate'] ?>%)</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="font-danger" data-feather="alert-circle" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Email Activity Over Time</h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Engagement Rates</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Open Rate</span>
                            <strong><?= $stats['open_rate'] ?>%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: <?= $stats['open_rate'] ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Click Rate</span>
                            <strong><?= $stats['click_rate'] ?>%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: <?= $stats['click_rate'] ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Bounce Rate</span>
                            <strong><?= $stats['bounce_rate'] ?>%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-danger" role="progressbar"
                                 style="width: <?= $stats['bounce_rate'] ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Unsubscribe Rate</span>
                            <strong><?= number_format($stats['unsubscribe_rate'], 2) ?>%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-secondary" role="progressbar"
                                 style="width: <?= $stats['unsubscribe_rate'] ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Campaign Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-right">Count</th>
                                    <th class="text-right">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Campaigns</td>
                                    <td class="text-right"><?= number_format($stats['total_campaigns'] ?? 0) ?></td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr>
                                    <td>Active Campaigns</td>
                                    <td class="text-right"><?= number_format($stats['active_campaigns'] ?? 0) ?></td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr>
                                    <td>Unique Opens</td>
                                    <td class="text-right"><?= number_format($stats['unique_opens'] ?? 0) ?></td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr>
                                    <td>Unique Clicks</td>
                                    <td class="text-right"><?= number_format($stats['unique_clicks'] ?? 0) ?></td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr>
                                    <td>Failed</td>
                                    <td class="text-right"><?= number_format($stats['failed'] ?? 0) ?></td>
                                    <td class="text-right">
                                        <?php
                                        $failRate = $stats['sent'] > 0 ? round(($stats['failed'] / $stats['sent']) * 100, 2) : 0;
                                        echo $failRate . '%';
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Unsubscribed</td>
                                    <td class="text-right"><?= number_format($stats['unsubscribed'] ?? 0) ?></td>
                                    <td class="text-right"><?= $stats['unsubscribe_rate'] ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Campaigns -->
    <?php if (!empty($topCampaigns)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Top Performing Campaigns</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Campaign</th>
                                        <th>Sent</th>
                                        <th>Open Rate</th>
                                        <th>Click Rate</th>
                                        <th>Bounce Rate</th>
                                        <th>Sent Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCampaigns as $campaign): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('/admin/email-marketing/campaigns/' . $campaign->id) ?>">
                                                    <?= htmlspecialchars($campaign->name) ?>
                                                </a>
                                            </td>
                                            <td><?= number_format($campaign->sent_count) ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?= $campaign->getOpenRate() ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    <?= $campaign->getClickRate() ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    <?= $campaign->getBounceRate() ?>%
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($campaign->sent_at ?? $campaign->created_at)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    feather.replace();

    // Activity Chart
    const activityData = <?= json_encode($chartData ?? ['labels' => [], 'sent' => [], 'opened' => [], 'clicked' => []]) ?>;
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: activityData.labels,
            datasets: [
                {
                    label: 'Sent',
                    data: activityData.sent,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Opened',
                    data: activityData.opened,
                    borderColor: 'rgb(255, 159, 64)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Clicked',
                    data: activityData.clicked,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Status Distribution Chart
    const statusData = <?= json_encode($stats) ?>;
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Sent', 'Opened', 'Clicked', 'Bounced', 'Failed'],
            datasets: [{
                data: [
                    statusData.sent,
                    statusData.opened,
                    statusData.clicked,
                    statusData.bounced,
                    statusData.failed || 0
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(201, 203, 207, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endsection
