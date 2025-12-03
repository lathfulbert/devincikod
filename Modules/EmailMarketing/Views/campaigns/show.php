@extends('backend.layouts.master')

@section('title', 'Campaign Details')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing/campaigns') ?>">Campaigns</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($campaign->name) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <!-- Campaign Header -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-2"><?= htmlspecialchars($campaign->name) ?></h4>
                            <p class="text-muted mb-0">
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
                                <span class="badge badge-<?= $statusClass ?> mr-2"><?= ucfirst($campaign->status) ?></span>
                                Subject: <strong><?= htmlspecialchars($campaign->subject) ?></strong>
                            </p>
                        </div>
                        <div>
                            <?php if (in_array($campaign->status, ['draft', 'scheduled'])): ?>
                                <a href="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/edit') ?>"
                                   class="btn btn-warning">
                                    <i data-feather="edit"></i> Edit
                                </a>
                            <?php endif; ?>

                            <?php if ($campaign->status === 'draft'): ?>
                                <form method="POST"
                                      action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/send') ?>"
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to send this campaign?');">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="send"></i> Send Now
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($campaign->status === 'sending'): ?>
                                <form method="POST"
                                      action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/pause') ?>"
                                      style="display: inline;">
                                    <button type="submit" class="btn btn-warning">
                                        <i data-feather="pause"></i> Pause
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($campaign->status === 'paused'): ?>
                                <form method="POST"
                                      action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/resume') ?>"
                                      style="display: inline;">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="play"></i> Resume
                                    </button>
                                </form>
                            <?php endif; ?>

                            <a href="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/analytics') ?>"
                               class="btn btn-info">
                                <i data-feather="bar-chart-2"></i> View Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h2 class="mb-0"><?= number_format($campaign->sent_count) ?></h2>
                            <p class="text-muted mb-0">Sent</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-primary" data-feather="send" style="width: 30px; height: 30px;"></i>
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
                            <h2 class="mb-0"><?= number_format($campaign->opened_count) ?></h2>
                            <p class="text-muted mb-0">Opened (<?= $campaign->getOpenRate() ?>%)</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-success" data-feather="eye" style="width: 30px; height: 30px;"></i>
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
                            <h2 class="mb-0"><?= number_format($campaign->clicked_count) ?></h2>
                            <p class="text-muted mb-0">Clicked (<?= $campaign->getClickRate() ?>%)</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-warning" data-feather="mouse-pointer" style="width: 30px; height: 30px;"></i>
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
                            <h2 class="mb-0"><?= number_format($campaign->bounced_count) ?></h2>
                            <p class="text-muted mb-0">Bounced (<?= $campaign->getBounceRate() ?>%)</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-danger" data-feather="alert-circle" style="width: 30px; height: 30px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Campaign Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Campaign Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="30%">Template:</th>
                                <td>
                                    <?php if ($campaign->template_id): ?>
                                        <a href="<?= url('/admin/email-marketing/templates/' . $campaign->template_id) ?>">
                                            View Template
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No template</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>From:</th>
                                <td>
                                    <?= $campaign->from_name ? htmlspecialchars($campaign->from_name) : 'Default' ?>
                                    &lt;<?= $campaign->from_email ? htmlspecialchars($campaign->from_email) : 'default@example.com' ?>&gt;
                                </td>
                            </tr>
                            <tr>
                                <th>Reply-To:</th>
                                <td><?= $campaign->reply_to ? htmlspecialchars($campaign->reply_to) : '<span class="text-muted">None</span>' ?></td>
                            </tr>
                            <tr>
                                <th>Personalization:</th>
                                <td>
                                    <?= $campaign->use_personalization ?
                                        '<span class="badge badge-success">Enabled</span>' :
                                        '<span class="badge badge-secondary">Disabled</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Recipients:</th>
                                <td><?= number_format($campaign->total_recipients ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td><?= date('d/m/Y H:i', strtotime($campaign->created_at)) ?></td>
                            </tr>
                            <?php if ($campaign->scheduled_at): ?>
                                <tr>
                                    <th>Scheduled:</th>
                                    <td><?= date('d/m/Y H:i', strtotime($campaign->scheduled_at)) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($campaign->sent_at): ?>
                                <tr>
                                    <th>Sent:</th>
                                    <td><?= date('d/m/Y H:i', strtotime($campaign->sent_at)) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Performance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Open Rate</span>
                            <span><strong><?= $campaign->getOpenRate() ?>%</strong></span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: <?= $campaign->getOpenRate() ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Click Rate</span>
                            <span><strong><?= $campaign->getClickRate() ?>%</strong></span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: <?= $campaign->getClickRate() ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Bounce Rate</span>
                            <span><strong><?= $campaign->getBounceRate() ?>%</strong></span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-danger" role="progressbar"
                                 style="width: <?= $campaign->getBounceRate() ?>%">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-4">
                            <h4><?= number_format($campaign->failed_count) ?></h4>
                            <p class="text-muted mb-0">Failed</p>
                        </div>
                        <div class="col-4">
                            <h4><?= number_format($campaign->unsubscribed_count) ?></h4>
                            <p class="text-muted mb-0">Unsubscribed</p>
                        </div>
                        <div class="col-4">
                            <h4>$<?= number_format($campaign->total_cost, 2) ?></h4>
                            <p class="text-muted mb-0">Total Cost</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Stats -->
    <?php if (!empty($stats)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Detailed Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center p-3 border-right">
                                    <h3><?= number_format($stats['delivered'] ?? 0) ?></h3>
                                    <p class="text-muted mb-0">Delivered</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border-right">
                                    <h3><?= number_format($stats['unique_opens'] ?? 0) ?></h3>
                                    <p class="text-muted mb-0">Unique Opens</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border-right">
                                    <h3><?= number_format($stats['unique_clicks'] ?? 0) ?></h3>
                                    <p class="text-muted mb-0">Unique Clicks</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <h3><?= $stats['avg_time_to_open'] ?? 'N/A' ?></h3>
                                    <p class="text-muted mb-0">Avg. Time to Open</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection
