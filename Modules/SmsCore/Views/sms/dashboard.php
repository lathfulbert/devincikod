@extends('backend.layouts.master')

@section('title', 'SMS Dashboard')

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
                    <li class="breadcrumb-item">SMS</li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- Container-fluid starts-->

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h2 class="mb-0"><?= number_format($stats['total_messages']) ?></h2>
                            <p class="text-muted mb-0">Total Messages</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-primary" data-feather="message-circle" style="width: 30px; height: 30px;"></i>
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
                            <h2 class="mb-0"><?= $stats['messages_today'] ?></h2>
                            <p class="text-muted mb-0">Messages Today</p>
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
                            <h2 class="mb-0"><?= $stats['success_rate'] ?>%</h2>
                            <p class="text-muted mb-0">Success Rate</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-warning" data-feather="check-circle" style="width: 30px; height: 30px;"></i>
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
                            <h2 class="mb-0"><?= number_format($stats['wallet_balance'], 0) ?></h2>
                            <p class="text-muted mb-0">Wallet Balance</p>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <i class="font-danger" data-feather="dollar-sign" style="width: 30px; height: 30px;"></i>
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
                            <a href="<?= url('/admin/sms/send') ?>" class="btn btn-primary btn-block">
                                <i data-feather="send"></i> Send SMS
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/admin/sms/bulk') ?>" class="btn btn-success btn-block">
                                <i data-feather="users"></i> Send Bulk SMS
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/admin/wallet/topup') ?>" class="btn btn-warning btn-block">
                                <i data-feather="credit-card"></i> Top-up Credits
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/admin/sms/history') ?>" class="btn btn-info btn-block">
                                <i data-feather="list"></i> View History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent SMS Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Recipient</th>
                                    <th>Gateway</th>
                                    <th>Status</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($recentMessages) && count($recentMessages) > 0): ?>
                                    <?php foreach ($recentMessages as $msg): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($msg->created_at)) ?></td>
                                            <td><?= htmlspecialchars(substr($msg->to, 0, 8)) ?>***</td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?= htmlspecialchars($msg->gateway ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                $statusText = ucfirst($msg->status);
                                                switch($msg->status) {
                                                    case 'sent':
                                                    case 'delivered':
                                                        $statusClass = 'success';
                                                        break;
                                                    case 'failed':
                                                        $statusClass = 'danger';
                                                        break;
                                                    case 'pending':
                                                        $statusClass = 'warning';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= htmlspecialchars($statusText) ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($msg->cost ?? 0.03, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i data-feather="inbox"></i>
                                            Aucune activité récente
                                        </td>
                                    </tr>
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