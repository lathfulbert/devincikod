@extends('backend.layouts.master')

@section('title', 'Mes Demandes de Rechargement')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Mes Demandes de Rechargement' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route('home') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= route('wallet.index') ?>">Wallet</a></li>
                    <li class="breadcrumb-item active">Mes Demandes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Actions rapides -->
            <div class="card mb-3">
                <div class="card-body">
                    <a href="<?= route('wallet.topup') ?>" class="btn btn-primary">
                        <i data-feather="plus"></i> Nouvelle Demande de Rechargement
                    </a>
                    <a href="<?= route('wallet.index') ?>" class="btn btn-secondary">
                        <i data-feather="arrow-left"></i> Retour au Wallet
                    </a>
                </div>
            </div>

            <!-- Liste des demandes -->
            <div class="card">
                <div class="card-header">
                    <h5>Historique de mes demandes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($requests) || count($requests) === 0): ?>
                        <div class="alert alert-info">
                            <i data-feather="info"></i>
                            <strong>Aucune demande de rechargement.</strong>
                            <p class="mb-0 mt-2">Vous n'avez pas encore effectué de demande de rechargement. Cliquez sur "Nouvelle Demande" pour commencer.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Détails</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td><strong>#<?= $request->id ?></strong></td>
                                            <td>
                                                <strong class="text-primary">
                                                    <?= number_format($request->amount, 0, ',', ' ') ?> XOF
                                                </strong>
                                            </td>
                                            <td>
                                                <?php
                                                $methodIcons = [
                                                    'gateway' => 'credit-card',
                                                    'cash' => 'dollar-sign',
                                                    'mobile_money' => 'smartphone',
                                                    'bank_transfer' => 'briefcase',
                                                    'other' => 'more-horizontal'
                                                ];
                                                $icon = $methodIcons[$request->payment_method] ?? 'help-circle';
                                                ?>
                                                <i data-feather="<?= $icon ?>" class="me-1"></i>
                                                <?= $request->getPaymentMethodLabel() ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $request->getStatusBadgeClass() ?>">
                                                    <?= $request->getStatusLabel() ?>
                                                </span>
                                                <?php if ($request->gateway_status && $request->payment_method === 'gateway'): ?>
                                                    <br><small class="text-muted">Gateway: <?= htmlspecialchars($request->gateway_status) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= date('d/m/Y', strtotime($request->created_at)) ?>
                                                    <br>
                                                    <?= date('H:i', strtotime($request->created_at)) ?>
                                                </small>
                                                <?php if ($request->reviewed_at): ?>
                                                    <br><small class="text-muted">
                                                        Révisé le <?= date('d/m/Y H:i', strtotime($request->reviewed_at)) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailsModal<?= $request->id ?>">
                                                    <i data-feather="eye"></i> Voir
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal Détails -->
                                        <div class="modal fade" id="detailsModal<?= $request->id ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Détails de la demande #<?= $request->id ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <dl class="row">
                                                            <dt class="col-sm-5">Montant :</dt>
                                                            <dd class="col-sm-7">
                                                                <strong class="text-primary">
                                                                    <?= number_format($request->amount, 0, ',', ' ') ?> XOF
                                                                </strong>
                                                            </dd>

                                                            <dt class="col-sm-5">Méthode de paiement :</dt>
                                                            <dd class="col-sm-7"><?= $request->getPaymentMethodLabel() ?></dd>

                                                            <dt class="col-sm-5">Statut :</dt>
                                                            <dd class="col-sm-7">
                                                                <span class="badge bg-<?= $request->getStatusBadgeClass() ?>">
                                                                    <?= $request->getStatusLabel() ?>
                                                                </span>
                                                            </dd>

                                                            <dt class="col-sm-5">Date de création :</dt>
                                                            <dd class="col-sm-7"><?= date('d/m/Y H:i:s', strtotime($request->created_at)) ?></dd>

                                                            <?php if ($request->gateway_transaction_id): ?>
                                                                <dt class="col-sm-5">ID Transaction :</dt>
                                                                <dd class="col-sm-7">
                                                                    <code><?= htmlspecialchars($request->gateway_transaction_id) ?></code>
                                                                </dd>
                                                            <?php endif; ?>

                                                            <?php if ($request->gateway_status): ?>
                                                                <dt class="col-sm-5">Statut Gateway :</dt>
                                                                <dd class="col-sm-7">
                                                                    <span class="badge bg-<?= $request->gateway_status === 'SUCCESS' ? 'success' : 'warning' ?>">
                                                                        <?= htmlspecialchars($request->gateway_status) ?>
                                                                    </span>
                                                                </dd>
                                                            <?php endif; ?>

                                                            <?php if ($request->notes): ?>
                                                                <dt class="col-sm-5">Mes notes :</dt>
                                                                <dd class="col-sm-7">
                                                                    <div class="alert alert-light mb-0">
                                                                        <?= nl2br(htmlspecialchars($request->notes)) ?>
                                                                    </div>
                                                                </dd>
                                                            <?php endif; ?>

                                                            <?php if ($request->reviewed_at): ?>
                                                                <dt class="col-sm-5">Révisé par :</dt>
                                                                <dd class="col-sm-7">
                                                                    <?= htmlspecialchars($request->reviewer->name ?? 'Admin') ?>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        Le <?= date('d/m/Y à H:i', strtotime($request->reviewed_at)) ?>
                                                                    </small>
                                                                </dd>
                                                            <?php endif; ?>

                                                            <?php if ($request->admin_notes): ?>
                                                                <dt class="col-sm-5">Notes de l'admin :</dt>
                                                                <dd class="col-sm-7">
                                                                    <div class="alert alert-<?= $request->status === 'rejected' ? 'danger' : 'success' ?> mb-0">
                                                                        <strong>
                                                                            <?php if ($request->status === 'rejected'): ?>
                                                                                <i data-feather="x-circle"></i> Raison du rejet :
                                                                            <?php else: ?>
                                                                                <i data-feather="check-circle"></i> Notes :
                                                                            <?php endif; ?>
                                                                        </strong>
                                                                        <br>
                                                                        <?= nl2br(htmlspecialchars($request->admin_notes)) ?>
                                                                    </div>
                                                                </dd>
                                                            <?php endif; ?>

                                                            <?php if ($request->proof_of_payment): ?>
                                                                <dt class="col-sm-5">Preuve de paiement :</dt>
                                                                <dd class="col-sm-7">
                                                                    <a href="<?= file_url($request->proof_of_payment) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i data-feather="file"></i> Télécharger
                                                                    </a>
                                                                </dd>
                                                            <?php endif; ?>

                                                            <dt class="col-sm-5">IP :</dt>
                                                            <dd class="col-sm-7">
                                                                <small class="text-muted"><?= htmlspecialchars($request->ip_address ?? 'N/A') ?></small>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Légende des statuts -->
                        <div class="mt-4">
                            <h6>Légende des statuts :</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><span class="badge bg-warning">En attente</span> - Demande en attente de validation admin</li>
                                        <li><span class="badge bg-info">En traitement</span> - Paiement en cours de traitement</li>
                                        <li><span class="badge bg-primary">Approuvée</span> - Demande approuvée par l'admin</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><span class="badge bg-success">Complétée</span> - Crédit ajouté à votre wallet</li>
                                        <li><span class="badge bg-danger">Rejetée</span> - Demande rejetée par l'admin</li>
                                        <li><span class="badge bg-secondary">Annulée</span> - Demande annulée</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Auto-refresh feather icons dans les modals
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            feather.replace();
        });
    });
</script>
@endsection
