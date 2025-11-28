@extends('backend.layouts.master')

@section('title', 'Wallet')

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
                    <li class="breadcrumb-item active">Wallet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Wallet Balance Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="text-white">Current Balance</h3>
                            <h1 class="text-white display-3">
                                $<?= number_format($wallet['balance'], 2) ?>
                            </h1>
                            <p class="text-white-50">Status: <?= ucfirst($wallet['status']) ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= url('/admin/wallet/topup') ?>" class="btn btn-light btn-lg">
                                <i data-feather="plus"></i> Top-up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Recent Transactions</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="<?= url('/admin/wallet/history') ?>" class="btn btn-sm btn-primary">
                                View All
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $txn): ?>
                                    <tr>
                                        <td><?= date('M d, Y H:i', strtotime($txn['created_at'])) ?></td>
                                        <td>
                                            <?php if ($txn['type'] === 'credit'): ?>
                                                <span class="badge badge-success">Credit</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Debit</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($txn['description']) ?></td>
                                        <td class="text-end">
                                            <?php if ($txn['type'] === 'credit'): ?>
                                                <span class="text-success">+$<?= number_format($txn['amount'], 2) ?></span>
                                            <?php else: ?>
                                                <span class="text-danger">-$<?= number_format($txn['amount'], 2) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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