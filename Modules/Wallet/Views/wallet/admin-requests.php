@extends('backend.layouts.master')

@section('title', 'Gestion des Demandes de Rechargement')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Gestion des Demandes de Rechargement' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/wallet') ?>">Wallet</a></li>
                    <li class="breadcrumb-item active">Admin - Demandes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Alert pour les demandes en attente -->
            <?php if ($pendingCount > 0): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i data-feather="alert-circle"></i>
                    <strong>Attention !</strong> Vous avez <strong><?= $pendingCount ?></strong> demande<?= $pendingCount > 1 ? 's' : '' ?> en attente de validation.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtres de statut -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <a href="<?= url('/admin/wallet/admin-requests') ?>"
                           class="btn btn-outline-primary <?= !isset($currentStatus) || $currentStatus === null ? 'active' : '' ?>">
                            <i data-feather="list"></i> Toutes
                        </a>
                        <a href="<?= url('/admin/wallet/admin-requests?status=pending') ?>"
                           class="btn btn-outline-warning <?= isset($currentStatus) && $currentStatus === 'pending' ? 'active' : '' ?>">
                            <i data-feather="clock"></i> En attente
                            <?php if ($pendingCount > 0): ?>
                                <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= url('/admin/wallet/admin-requests?status=approved') ?>"
                           class="btn btn-outline-primary <?= isset($currentStatus) && $currentStatus === 'approved' ? 'active' : '' ?>">
                            <i data-feather="thumbs-up"></i> Approuvées
                        </a>
                        <a href="<?= url('/admin/wallet/admin-requests?status=completed') ?>"
                           class="btn btn-outline-success <?= isset($currentStatus) && $currentStatus === 'completed' ? 'active' : '' ?>">
                            <i data-feather="check-circle"></i> Complétées
                        </a>
                        <a href="<?= url('/admin/wallet/admin-requests?status=rejected') ?>"
                           class="btn btn-outline-danger <?= isset($currentStatus) && $currentStatus === 'rejected' ? 'active' : '' ?>">
                            <i data-feather="x-circle"></i> Rejetées
                        </a>
                        <a href="<?= url('/admin/wallet/admin-requests?status=processing') ?>"
                           class="btn btn-outline-info <?= isset($currentStatus) && $currentStatus === 'processing' ? 'active' : '' ?>">
                            <i data-feather="loader"></i> En traitement
                        </a>
                    </div>
                </div>
            </div>

            <!-- Liste des demandes -->
            <div class="card">
                <div class="card-header">
                    <h5>
                        <?php if (isset($currentStatus) && $currentStatus): ?>
                            Demandes : <?= ucfirst($currentStatus) ?>
                        <?php else: ?>
                            Toutes les demandes
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($requests) || count($requests) === 0): ?>
                        <div class="alert alert-info">
                            <i data-feather="info"></i>
                            <strong>Aucune demande trouvée.</strong>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="requestsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Utilisateur</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <tr class="<?= $request->isPending() ? 'table-warning' : '' ?>">
                                            <td><strong>#<?= $request->id ?></strong></td>
                                            <td>
                                                <strong><?= htmlspecialchars($request->user->name ?? 'N/A') ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($request->user->email ?? '') ?></small>
                                            </td>
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
                                                <?php if ($request->gateway_transaction_id): ?>
                                                    <br><small class="text-muted">
                                                        <code><?= htmlspecialchars(substr($request->gateway_transaction_id, 0, 20)) ?>...</code>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $request->getStatusBadgeClass() ?>">
                                                    <?= $request->getStatusLabel() ?>
                                                </span>
                                                <?php if ($request->gateway_status): ?>
                                                    <br><small class="text-muted">GW: <?= htmlspecialchars($request->gateway_status) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= date('d/m/Y', strtotime($request->created_at)) ?>
                                                    <br>
                                                    <?= date('H:i', strtotime($request->created_at)) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($request->notes): ?>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars(substr($request->notes, 0, 50)) ?>
                                                        <?= strlen($request->notes) > 50 ? '...' : '' ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($request->isPending()): ?>
                                                    <!-- Bouton Approuver -->
                                                    <button class="btn btn-sm btn-success mb-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#approveModal<?= $request->id ?>"
                                                            title="Approuver">
                                                        <i data-feather="check"></i>
                                                    </button>

                                                    <!-- Bouton Rejeter -->
                                                    <button class="btn btn-sm btn-danger mb-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal<?= $request->id ?>"
                                                            title="Rejeter">
                                                        <i data-feather="x"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <?php if ($request->reviewed_by): ?>
                                                        <small class="text-muted">
                                                            <i data-feather="user"></i>
                                                            <?= htmlspecialchars($request->reviewer->name ?? 'Admin') ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <!-- Bouton Détails -->
                                                <button class="btn btn-sm btn-outline-info mb-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailsModal<?= $request->id ?>"
                                                        title="Voir détails">
                                                    <i data-feather="eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal Approuver -->
                                        <div class="modal fade" id="approveModal<?= $request->id ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="<?= url('/admin/wallet/approve/' . $request->id) ?>">
                                                        <?= csrf_field() ?>
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title">
                                                                <i data-feather="check-circle"></i>
                                                                Approuver la demande #<?= $request->id ?>
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning">
                                                                <strong>Attention :</strong> Cette action créditera automatiquement le wallet de l'utilisateur.
                                                            </div>

                                                            <dl class="row">
                                                                <dt class="col-sm-5">Utilisateur :</dt>
                                                                <dd class="col-sm-7">
                                                                    <strong><?= htmlspecialchars($request->user->name ?? 'N/A') ?></strong>
                                                                </dd>

                                                                <dt class="col-sm-5">Montant à créditer :</dt>
                                                                <dd class="col-sm-7">
                                                                    <strong class="text-success">
                                                                        <?= number_format($request->amount, 0, ',', ' ') ?> XOF
                                                                    </strong>
                                                                </dd>

                                                                <dt class="col-sm-5">Méthode de paiement :</dt>
                                                                <dd class="col-sm-7"><?= $request->getPaymentMethodLabel() ?></dd>

                                                                <?php if ($request->notes): ?>
                                                                    <dt class="col-sm-5">Notes utilisateur :</dt>
                                                                    <dd class="col-sm-7">
                                                                        <div class="alert alert-light mb-0">
                                                                            <?= nl2br(htmlspecialchars($request->notes)) ?>
                                                                        </div>
                                                                    </dd>
                                                                <?php endif; ?>
                                                            </dl>

                                                            <div class="mb-3">
                                                                <label for="admin_notes_approve_<?= $request->id ?>" class="form-label">
                                                                    Notes administrateur (optionnel)
                                                                </label>
                                                                <textarea name="admin_notes"
                                                                          id="admin_notes_approve_<?= $request->id ?>"
                                                                          class="form-control"
                                                                          rows="3"
                                                                          placeholder="Ex: Paiement vérifié avec la banque, référence OK, etc."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-success">
                                                                <i data-feather="check"></i> Approuver et créditer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Rejeter -->
                                        <div class="modal fade" id="rejectModal<?= $request->id ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="<?= url('/admin/wallet/reject/' . $request->id) ?>" id="rejectForm<?= $request->id ?>">
                                                        <?= csrf_field() ?>
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">
                                                                <i data-feather="x-circle"></i>
                                                                Rejeter la demande #<?= $request->id ?>
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-danger">
                                                                <strong>Attention :</strong> Cette demande sera rejetée et aucun crédit ne sera ajouté.
                                                            </div>

                                                            <dl class="row">
                                                                <dt class="col-sm-5">Utilisateur :</dt>
                                                                <dd class="col-sm-7">
                                                                    <strong><?= htmlspecialchars($request->user->name ?? 'N/A') ?></strong>
                                                                </dd>

                                                                <dt class="col-sm-5">Montant :</dt>
                                                                <dd class="col-sm-7">
                                                                    <strong><?= number_format($request->amount, 0, ',', ' ') ?> XOF</strong>
                                                                </dd>
                                                            </dl>

                                                            <div class="mb-3">
                                                                <label for="admin_notes_reject_<?= $request->id ?>" class="form-label">
                                                                    Raison du rejet <span class="text-danger">*</span>
                                                                </label>
                                                                <textarea name="admin_notes"
                                                                          id="admin_notes_reject_<?= $request->id ?>"
                                                                          class="form-control"
                                                                          rows="3"
                                                                          required
                                                                          placeholder="Ex: Preuve de paiement invalide, référence introuvable, montant incorrect, etc."></textarea>
                                                                <small class="text-muted">
                                                                    Cette raison sera visible par l'utilisateur.
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-danger">
                                                                <i data-feather="x"></i> Rejeter la demande
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Détails -->
                                        <div class="modal fade" id="detailsModal<?= $request->id ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Détails de la demande #<?= $request->id ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="border-bottom pb-2">Informations de la demande</h6>
                                                                <dl class="row">
                                                                    <dt class="col-sm-6">ID :</dt>
                                                                    <dd class="col-sm-6">#<?= $request->id ?></dd>

                                                                    <dt class="col-sm-6">Montant :</dt>
                                                                    <dd class="col-sm-6">
                                                                        <strong class="text-primary">
                                                                            <?= number_format($request->amount, 0, ',', ' ') ?> XOF
                                                                        </strong>
                                                                    </dd>

                                                                    <dt class="col-sm-6">Méthode :</dt>
                                                                    <dd class="col-sm-6"><?= $request->getPaymentMethodLabel() ?></dd>

                                                                    <dt class="col-sm-6">Statut :</dt>
                                                                    <dd class="col-sm-6">
                                                                        <span class="badge bg-<?= $request->getStatusBadgeClass() ?>">
                                                                            <?= $request->getStatusLabel() ?>
                                                                        </span>
                                                                    </dd>

                                                                    <dt class="col-sm-6">Créée le :</dt>
                                                                    <dd class="col-sm-6">
                                                                        <?= date('d/m/Y à H:i:s', strtotime($request->created_at)) ?>
                                                                    </dd>

                                                                    <?php if ($request->gateway_transaction_id): ?>
                                                                        <dt class="col-sm-6">ID Transaction :</dt>
                                                                        <dd class="col-sm-6">
                                                                            <code><?= htmlspecialchars($request->gateway_transaction_id) ?></code>
                                                                        </dd>
                                                                    <?php endif; ?>

                                                                    <?php if ($request->gateway_status): ?>
                                                                        <dt class="col-sm-6">Statut Gateway :</dt>
                                                                        <dd class="col-sm-6">
                                                                            <span class="badge bg-<?= $request->gateway_status === 'SUCCESS' ? 'success' : 'warning' ?>">
                                                                                <?= htmlspecialchars($request->gateway_status) ?>
                                                                            </span>
                                                                        </dd>
                                                                    <?php endif; ?>
                                                                </dl>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <h6 class="border-bottom pb-2">Utilisateur</h6>
                                                                <dl class="row">
                                                                    <dt class="col-sm-6">Nom :</dt>
                                                                    <dd class="col-sm-6">
                                                                        <strong><?= htmlspecialchars($request->user->name ?? 'N/A') ?></strong>
                                                                    </dd>

                                                                    <dt class="col-sm-6">Email :</dt>
                                                                    <dd class="col-sm-6">
                                                                        <?= htmlspecialchars($request->user->email ?? 'N/A') ?>
                                                                    </dd>

                                                                    <dt class="col-sm-6">Wallet ID :</dt>
                                                                    <dd class="col-sm-6">#<?= $request->wallet_id ?></dd>

                                                                    <dt class="col-sm-6">IP :</dt>
                                                                    <dd class="col-sm-6">
                                                                        <code><?= htmlspecialchars($request->ip_address ?? 'N/A') ?></code>
                                                                    </dd>

                                                                    <?php if ($request->user_agent): ?>
                                                                        <dt class="col-sm-6">User Agent :</dt>
                                                                        <dd class="col-sm-6">
                                                                            <small class="text-muted">
                                                                                <?= htmlspecialchars(substr($request->user_agent, 0, 50)) ?>...
                                                                            </small>
                                                                        </dd>
                                                                    <?php endif; ?>
                                                                </dl>
                                                            </div>
                                                        </div>

                                                        <?php if ($request->notes): ?>
                                                            <div class="mt-3">
                                                                <h6 class="border-bottom pb-2">Notes de l'utilisateur</h6>
                                                                <div class="alert alert-light">
                                                                    <?= nl2br(htmlspecialchars($request->notes)) ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ($request->reviewed_at): ?>
                                                            <div class="mt-3">
                                                                <h6 class="border-bottom pb-2">Révision administrateur</h6>
                                                                <dl class="row">
                                                                    <dt class="col-sm-4">Révisé par :</dt>
                                                                    <dd class="col-sm-8">
                                                                        <strong><?= htmlspecialchars($request->reviewer->name ?? 'Admin') ?></strong>
                                                                    </dd>

                                                                    <dt class="col-sm-4">Date de révision :</dt>
                                                                    <dd class="col-sm-8">
                                                                        <?= date('d/m/Y à H:i:s', strtotime($request->reviewed_at)) ?>
                                                                    </dd>

                                                                    <?php if ($request->admin_notes): ?>
                                                                        <dt class="col-sm-4">Notes admin :</dt>
                                                                        <dd class="col-sm-8">
                                                                            <div class="alert alert-<?= $request->status === 'rejected' ? 'danger' : 'success' ?> mb-0">
                                                                                <?= nl2br(htmlspecialchars($request->admin_notes)) ?>
                                                                            </div>
                                                                        </dd>
                                                                    <?php endif; ?>
                                                                </dl>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ($request->proof_of_payment): ?>
                                                            <div class="mt-3">
                                                                <h6 class="border-bottom pb-2">Preuve de paiement</h6>
                                                                <a href="<?= file_url($request->proof_of_payment) ?>" target="_blank" class="btn btn-outline-primary">
                                                                    <i data-feather="download"></i> Télécharger la preuve
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
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

    // Validation formulaire de rejet
    document.querySelectorAll('[id^="rejectForm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const textarea = this.querySelector('textarea[name="admin_notes"]');
            if (!textarea.value.trim()) {
                e.preventDefault();
                alert('Veuillez indiquer une raison pour le rejet');
                textarea.focus();
                return false;
            }
        });
    });

    // Confirmation avant approbation
    document.querySelectorAll('[id^="approveModal"]').forEach(modal => {
        modal.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir approuver cette demande ? Le wallet sera crédité automatiquement.')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Confirmation avant rejet
    document.querySelectorAll('[id^="rejectModal"]').forEach(modal => {
        modal.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir rejeter cette demande ?')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endsection
