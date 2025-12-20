@extends('backend.layouts.master')

@section('title', 'Email Marketing Dashboard')

@section('content')

<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Email Marketing</li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- Container-fluid ends-->

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h2 class="mb-0"><?= number_format($stats['total_emails']) ?></h2>
                            <p class="text-muted mb-0">Total Emails</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-primary" data-feather="mail" style="width: 30px; height: 30px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h2 class="mb-0"><?= number_format($stats['sent']) ?></h2>
                            <p class="text-muted mb-0">Emails Sent</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-success" data-feather="send" style="width: 30px; height: 30px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h2 class="mb-0"><?= $stats['open_rate'] ?>%</h2>
                            <p class="text-muted mb-0">Open Rate</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-warning" data-feather="eye" style="width: 30px; height: 30px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h2 class="mb-0"><?= $stats['click_rate'] ?>%</h2>
                            <p class="text-muted mb-0">Click Rate</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-info" data-feather="mouse-pointer" style="width: 30px; height: 30px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="<?= route('admin.email-marketing.campaigns.create') ?>" class="btn btn-primary btn-block">
                                <i data-feather="plus-circle"></i> New Campaign
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= route('admin.email-marketing.templates.create') ?>" class="btn btn-success btn-block">
                                <i data-feather="file-text"></i> New Template
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= route('admin.email-marketing.workflows.create') ?>" class="btn btn-warning btn-block">
                                <i data-feather="git-branch"></i> New Workflow
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= route('admin.email-marketing.analytics') ?>" class="btn btn-info btn-block">
                                <i data-feather="bar-chart-2"></i> View Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Campaigns -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Campaigns</h5>
                    <a href="<?= route('admin.email-marketing.campaigns.index') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Sent</th>
                                    <th>Open Rate</th>
                                    <th>Click Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentCampaigns)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No campaigns found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentCampaigns as $campaign): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($campaign->name) ?></strong></td>
                                            <td>
                                                <?php
                                                $statusClass = match($campaign->status) {
                                                    'draft' => 'secondary',
                                                    'scheduled' => 'info',
                                                    'sending' => 'warning',
                                                    'completed' => 'success',
                                                    'paused' => 'dark',
                                                    'failed' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($campaign->status) ?></span>
                                            </td>
                                            <td><?= number_format($campaign->sent_count) ?></td>
                                            <td><?= $campaign->getOpenRate() ?>%</td>
                                            <td><?= $campaign->getClickRate() ?>%</td>
                                            <td>
                                                <a href="<?= route('admin.email-marketing.campaigns.show', ['id' => $campaign->id]) ?>" class="btn btn-sm btn-info" title="View details">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Top Performers (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($topPerformers)): ?>
                        <p class="text-center text-muted py-4">No data available</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($topPerformers as $performer): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($performer->name) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Open: <?= $performer->getOpenRate() ?>% | Click: <?= $performer->getClickRate() ?>%
                                        </small>
                                    </div>
                                    <span class="badge badge-primary badge-pill"><?= number_format($performer->sent_count) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Email Activity (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="emailActivityChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    feather.replace();

    // Email Activity Chart
    const chartData = <?= json_encode($chartData) ?>;

    const ctx = document.getElementById('emailActivityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Sent',
                    data: chartData.sent,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Opened',
                    data: chartData.opened,
                    borderColor: 'rgb(255, 159, 64)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Clicked',
                    data: chartData.clicked,
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
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
