@extends('backend.layouts.master')

@section('title', 'Historique de Facturation SMS')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Facturation SMS</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">SMS</li>
                    <li class="breadcrumb-item active">Facturation</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Historique des Transactions</h5>
                        <div>
                            <span class="badge badge-primary">Total: <?= $totalLogs ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Destinataire</th>
                                    <th>Type</th>
                                    <th>Segments</th>
                                    <th>Coût Total</th>
                                    <th>Statut</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Aucune transaction trouvée.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                if (is_object($log->created_at)) {
                                                    echo $log->created_at->format('d/m/Y H:i');
                                                } elseif ($log->created_at) {
                                                    echo date('d/m/Y H:i', strtotime($log->created_at));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($log->user): ?>
                                                    <?= htmlspecialchars($log->user->username ?? $log->user->email) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Inconnu (<?= $log->user_id ?>)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($log->recipient) ?></td>
                                            <td><span class="badge badge-light text-dark"><?= strtoupper($log->sms_type) ?></span></td>
                                            <td><?= $log->segments ?></td>
                                            <td>
                                                <strong><?= number_format($log->total_cost, 2) ?> <?= $log->currency ?></strong>
                                                <br>
                                                <small class="text-muted"><?= number_format($log->unit_cost, 2) ?> / unit</small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = match ($log->status) {
                                                    'paid' => 'success',
                                                    'pending' => 'warning',
                                                    'failed' => 'danger',
                                                    'refunded' => 'info',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($log->status) ?></span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= $log->country_code ?> - <?= $log->operator ?><br>
                                                    Gateway: <?= $log->gateway ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="mt-3">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Précédent</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
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
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endsection