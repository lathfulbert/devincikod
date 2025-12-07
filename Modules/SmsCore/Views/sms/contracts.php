@extends('backend.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        </div>
        <div class="col-auto">
            <a href="/admin/sms/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détails des Contrats Orange</h6>
        </div>
        <div class="card-body">
            <?php if (empty($contracts)): ?>
                <div class="text-center py-4">
                    <p class="text-muted">Aucun contrat disponible ou erreur de récupération.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Description</th>
                                <th>Date Expiration</th>
                                <th>Unités Restantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract): ?>
                                <tr>
                                    <td><?= htmlspecialchars($contract['offerName'] ?? $contract['id'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($contract['type'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (isset($contract['expirationDate'])): ?>
                                            <?= date('d/m/Y H:i', strtotime($contract['expirationDate'])) ?>
                                        <?php else: ?>
                                            <span class="badge badge-success">Illimité</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($contract['availableUnits'])): ?>
                                            <span class="font-weight-bold text-primary"><?= $contract['availableUnits'] ?></span>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
@endsection