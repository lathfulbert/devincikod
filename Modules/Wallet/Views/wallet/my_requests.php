@extends('backend.layouts.master')

@section('title', 'Mes Demandes Wallet')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Mes Demandes Wallet</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route('dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= route('wallet.index') ?>">Wallet</a></li>
                    <li class="breadcrumb-item active">Mes Demandes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <!-- Bouton nouvelle demande -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="<?= route('wallet.topup') ?>" class="btn btn-success">
                <i class="icon-plus"></i> Nouvelle Demande de Recharge
            </a>
        </div>
    </div>

    <!-- Liste des demandes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Mes Demandes de Recharge</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date de Demande</th>
                                    <th>Date de Traitement</th>
                                    <th>Commentaire</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="icon-list font-24"></i>
                                                <p class="mt-2">Aucune demande trouvée.</p>
                                                <a href="<?= route('wallet.topup') ?>" class="btn btn-sm btn-primary">
                                                    Faire ma première demande
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td><strong>#<?= $request->id ?></strong></td>
                                            <td>
                                                <span class="font-weight-bold text-success">
                                                    <?= number_format($request->amount, 2) ?> XOF
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = match($request->status) {
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    default => 'secondary'
                                                };
                                                $statusText = match($request->status) {
                                                    'pending' => 'En Attente',
                                                    'approved' => 'Approuvée',
                                                    'rejected' => 'Rejetée',
                                                    default => 'Inconnu'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($request->created_at)) ?></td>
                                            <td>
                                                <?php if ($request->processed_at): ?>
                                                    <?= date('d/m/Y H:i', strtotime($request->processed_at)) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($request->admin_comment): ?>
                                                    <span title="<?= htmlspecialchars($request->admin_comment) ?>">
                                                        <?= htmlspecialchars(substr($request->admin_comment, 0, 50)) ?>
                                                        <?= strlen($request->admin_comment) > 50 ? '...' : '' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($request->status === 'pending'): ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelRequest(<?= $request->id ?>)">
                                                        <i class="icon-x"></i> Annuler
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
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

<script>
function cancelRequest(requestId) {
    if (confirm('Êtes-vous sûr de vouloir annuler cette demande ?')) {
        // Créer un formulaire temporaire pour la soumission
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url("/admin/wallet/cancel-request") ?>';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'request_id';
        idInput.value = requestId;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '<?= csrf_token() ?>';

        form.appendChild(idInput);
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection