@extends('backend.layouts.master')

@section('title', 'Email Campaigns')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Email Campaigns' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item active">Campaigns</li>
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
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Email Campaigns</h5>
                    <a href="<?= url('/admin/email-marketing/campaigns/create') ?>" class="btn btn-primary">
                        <i data-feather="plus"></i> New Campaign
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?= empty($currentStatus) ? 'active' : '' ?>"
                               href="<?= url('/admin/email-marketing/campaigns') ?>">
                                All
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentStatus === 'draft' ? 'active' : '' ?>"
                               href="<?= url('/admin/email-marketing/campaigns?status=draft') ?>">
                                Draft
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentStatus === 'scheduled' ? 'active' : '' ?>"
                               href="<?= url('/admin/email-marketing/campaigns?status=scheduled') ?>">
                                Scheduled
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentStatus === 'sending' ? 'active' : '' ?>"
                               href="<?= url('/admin/email-marketing/campaigns?status=sending') ?>">
                                Sending
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentStatus === 'completed' ? 'active' : '' ?>"
                               href="<?= url('/admin/email-marketing/campaigns?status=completed') ?>">
                                Completed
                            </a>
                        </li>
                    </ul>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Recipients</th>
                                    <th>Sent</th>
                                    <th>Opened</th>
                                    <th>Clicked</th>
                                    <th>Progress</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($campaigns)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">No campaigns found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($campaigns as $campaign): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($campaign->name) ?></strong></td>
                                            <td><?= htmlspecialchars($campaign->subject) ?></td>
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
                                            <td><?= number_format($campaign->total_recipients ?? 0) ?></td>
                                            <td><?= number_format($campaign->sent_count) ?></td>
                                            <td><?= number_format($campaign->opened_count) ?> (<?= $campaign->getOpenRate() ?>%)</td>
                                            <td><?= number_format($campaign->clicked_count) ?> (<?= $campaign->getClickRate() ?>%)</td>
                                            <td>
                                                <?php
                                                $total = $campaign->total_recipients ?? 0;
                                                $progress = $total > 0 ? round(($campaign->sent_count / $total) * 100) : 0;
                                                ?>
                                                <div class="progress" style="height: 20px; min-width: 80px;">
                                                    <div class="progress-bar bg-<?= $statusClass ?>" role="progressbar"
                                                         style="width: <?= $progress ?>%">
                                                        <?= $progress ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($campaign->created_at)) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= url('/admin/email-marketing/campaigns/' . $campaign->id) ?>"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i data-feather="eye"></i>
                                                    </a>

                                                    <?php if (in_array($campaign->status, ['draft', 'scheduled'])): ?>
                                                        <a href="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/edit') ?>"
                                                           class="btn btn-sm btn-warning" title="Edit">
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($campaign->status === 'draft'): ?>
                                                        <form method="POST"
                                                              action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/send') ?>"
                                                              style="display: inline;"
                                                              onsubmit="return confirm('Are you sure you want to send this campaign?');">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Send">
                                                                <i data-feather="send"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($campaign->status === 'sending'): ?>
                                                        <form method="POST"
                                                              action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/pause') ?>"
                                                              style="display: inline;">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="Pause">
                                                                <i data-feather="pause"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($campaign->status === 'paused'): ?>
                                                        <form method="POST"
                                                              action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/resume') ?>"
                                                              style="display: inline;">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Resume">
                                                                <i data-feather="play"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($campaign->status === 'draft'): ?>
                                                        <form method="POST"
                                                              action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id . '/delete') ?>"
                                                              style="display: inline;"
                                                              onsubmit="return confirm('Are you sure you want to delete this campaign?');">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                <i data-feather="trash-2"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
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
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection
