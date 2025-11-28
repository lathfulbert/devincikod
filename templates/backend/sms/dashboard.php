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
                            <h2 class="mb-0">$<?= number_format($stats['wallet_balance'], 2) ?></h2>
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
                                <tr>
                                    <td><?= date('H:i:s') ?></td>
                                    <td>+1234567890</td>
                                    <td><span class="badge badge-primary">Infobip</span></td>
                                    <td><span class="badge badge-success">Delivered</span></td>
                                    <td>$0.03</td>
                                </tr>
                                <tr>
                                    <td><?= date('H:i:s', strtotime('-5 minutes')) ?></td>
                                    <td>+0987654321</td>
                                    <td><span class="badge badge-info">OrangeSMS</span></td>
                                    <td><span class="badge badge-warning">Sent</span></td>
                                    <td>$0.04</td>
                                </tr>
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