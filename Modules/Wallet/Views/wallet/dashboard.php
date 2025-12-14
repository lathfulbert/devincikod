@extends('backend.layouts.master')

@section('title', 'Wallet Dashboard')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Dashboard Wallet</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Wallet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <!-- Statistiques générales -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-14">Solde Total</h5>
                            <h4 class="m-0 text-success">
                                <span class="counter"><?= number_format($stats['total_balance'], 2) ?></span> XOF
                            </h4>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-wallet font-30 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-14">Utilisateurs avec Wallet</h5>
                            <h4 class="m-0 text-primary">
                                <span class="counter"><?= $stats['total_users'] ?></span>
                            </h4>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-user font-30 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-14">Demandes en Attente</h5>
                            <h4 class="m-0 text-warning">
                                <span class="counter"><?= $stats['pending_requests'] ?></span>
                            </h4>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-clock font-30 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-14">Mon Solde</h5>
                            <h4 class="m-0 text-info">
                                <span class="counter"><?= $userWallet ? number_format($userWallet->balance, 2) : '0.00' ?></span> XOF
                            </h4>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-credit-card font-30 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="<?= url('/admin/wallet/topup') ?>" class="btn btn-success btn-lg btn-block">
                                <i class="icon-plus"></i> Recharger mon Wallet
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/admin/wallet/requests') ?>" class="btn btn-primary btn-lg btn-block">
                                <i class="icon-list"></i> Mes Demandes
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/admin/wallet/manage') ?>" class="btn btn-info btn-lg btn-block">
                                <i class="icon-settings"></i> Gérer les Wallets
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/admin/wallet/admin-requests') ?>" class="btn btn-warning btn-lg btn-block">
                                <i class="icon-check-circle"></i> Gestion Demandes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions récentes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Transactions Récentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Description</th>
                                    <th>Solde Après</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stats['recent_transactions'])): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Aucune transaction récente.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($stats['recent_transactions'] as $transaction): ?>
                                        <tr>
                                            <td><?= $transaction->created_at ? date('d/m/Y H:i', strtotime($transaction->created_at)) : '-' ?></td>
                                            <td>
                                                <span class="badge badge-<?= ($transaction->type ?? 'unknown') === 'credit' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($transaction->type ?? 'unknown') ?>
                                                </span>
                                            </td>
                                            <td class="font-weight-bold">
                                                <span class="<?= ($transaction->type ?? 'unknown') === 'credit' ? 'text-success' : 'text-danger' ?>">
                                                    <?= ($transaction->type ?? 'unknown') === 'credit' ? '+' : '-' ?><?= number_format($transaction->amount ?? 0, 2) ?> XOF
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($transaction->description ?? '') ?></td>
                                            <td><strong><?= number_format($transaction->balance_after ?? 0, 2) ?> XOF</strong></td>
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