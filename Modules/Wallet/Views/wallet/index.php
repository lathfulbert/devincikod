@extends('backend.layouts.master')

@section('title', 'Wallet Management')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Gestion des Wallets</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Wallets</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Liste des Wallets Utilisateurs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utilisateur</th>
                                    <th>Solde</th>
                                    <th>Devise</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($wallets)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Aucun wallet trouvé.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($wallets as $wallet): ?>
                                        <tr>
                                            <td><?= $wallet['id'] ?></td>
                                            <td>
                                                <?php if (isset($wallet['user'])): ?>
                                                    <?= htmlspecialchars($wallet['user']['username'] ?? $wallet['user']['email']) ?>
                                                <?php else: ?>
                                                    User ID: <?= $wallet['user_id'] ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold">
                                                <?= number_format($wallet['balance'], 2) ?> <?= $wallet['currency'] ?>
                                            </td>
                                            <td><?= $wallet['currency'] ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match ($wallet['status']) {
                                                    'active' => 'success',
                                                    'frozen' => 'warning',
                                                    'suspended' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($wallet['status']) ?></span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($wallet['created_at'])) ?></td>
                                            <td>
                                                <form method="POST" action="<?= url('/admin/wallet/topup') ?>" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="user_id" value="<?= $wallet['user_id'] ?>">
                                                    <input type="number" name="amount" placeholder="Montant" class="form-control form-control-sm d-inline" style="width: 100px;" min="1" required>
                                                    <input type="text" name="description" placeholder="Description" class="form-control form-control-sm d-inline" style="width: 150px;">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Créditer">
                                                        <i data-feather="plus"></i> Créditer
                                                    </button>
                                                </form>

                                                <form method="POST" action="<?= url('/admin/wallet/debit') ?>" class="d-inline mt-2">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="user_id" value="<?= $wallet['user_id'] ?>">
                                                    <input type="number" name="amount" placeholder="Montant" class="form-control form-control-sm d-inline" style="width: 100px;" min="1" required>
                                                    <input type="text" name="description" placeholder="Description" class="form-control form-control-sm d-inline" style="width: 150px;">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Débiter">
                                                        <i data-feather="minus"></i> Débiter
                                                    </button>
                                                </form>
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
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endsection