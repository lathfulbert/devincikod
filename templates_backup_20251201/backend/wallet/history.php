@extends('backend.layouts.master')

@section('title', 'Transaction History')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Transaction History' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/wallet') ?>">Wallet</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>All Transactions</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="<?= url('/admin/wallet/topup') ?>" class="btn btn-sm btn-primary">
                                <i data-feather="plus-circle"></i> Top-up Wallet
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No transactions found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $trx): ?>
                                        <tr>
                                            <td><?= $trx['id'] ?></td>
                                            <td>
                                                <?php if ($trx['type'] === 'credit'): ?>
                                                    <span class="badge badge-success">Credit</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Debit</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="<?= $trx['type'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                                                    <?= $trx['type'] === 'credit' ? '+' : '-' ?>$<?= number_format($trx['amount'], 2) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($trx['description']) ?></td>
                                            <td>
                                                <span class="badge badge-light-primary">Completed</span>
                                            </td>
                                            <td><?= date('M d, Y H:i', strtotime($trx['created_at'])) ?></td>
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