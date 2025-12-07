@extends('backend.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        </div>
        <div class="col-auto">
            <a href="<?= url('/admin/sms/providers') ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Fournisseurs
            </a>
        </div>
    </div>

    <?php if ($contracts_error): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Erreur Contrats: <?= htmlspecialchars($contracts_error) ?>
        </div>
    <?php endif; ?>

    <?php if ($stats_error): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Erreur Statistiques: <?= htmlspecialchars($stats_error) ?>
        </div>
    <?php endif; ?>

    <!-- CONTRACTS SECTION -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Contrats & Solde</h6>
        </div>
        <div class="card-body">
            <?php if (empty($contracts)): ?>
                <p class="text-muted">Aucun contrat disponible.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Type</th>
                                <th>Expiration</th>
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

    <!-- STATISTICS SECTION -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Statistiques d'Usage (Direct Fournisseur)</h6>
        </div>
        <div class="card-body">
            <?php if (empty($statistics) || !isset($statistics['partnerStatistics']['statistics'])): ?>
                <div class="text-center py-4">
                    <p class="text-muted">Aucune statistique disponible.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <!-- Global Stats Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Developer ID</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($statistics['partnerStatistics']['developerId'] ?? 'N/A') ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-id-badge fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Pays</th>
                                <th>Application ID</th>
                                <th>Usage (SMS envoyés)</th>
                                <th>Rejets (Enforcements)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statistics['partnerStatistics']['statistics'] as $stat): ?>
                                <?php $serviceName = $stat['service']; ?>
                                <?php foreach ($stat['serviceStatistics'] as $serviceStat): ?>
                                    <?php $country = $serviceStat['country']; ?>
                                    <?php foreach ($serviceStat['countryStatistics'] as $appStat): ?>
                                        <tr>
                                            <td><span class="badge badge-primary"><?= htmlspecialchars($serviceName) ?></span></td>
                                            <td><?= htmlspecialchars($country) ?></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($appStat['appid']) ?></small></td>
                                            <td>
                                                <span class="font-weight-bold text-success">
                                                    <?= number_format($appStat['usage']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-warning">
                                                    <?= number_format($appStat['nbEnforcements']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PURCHASE HISTORY SECTION -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Historique des Achats</h6>
        </div>
        <div class="card-body">
            <?php if ($orders_error): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Erreur: <?= htmlspecialchars($orders_error) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <p class="text-muted">Aucun historique d'achat disponible.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Offre</th>
                                <th>Unités (Avant &rarr; Après)</th>
                                <th>Montant</th>
                                <th>Commentaire</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <?= isset($order['purchaseDate']) ? date('d/m/Y H:i', strtotime($order['purchaseDate'])) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= htmlspecialchars($order['type'] ?? 'N/A') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($order['offerName'] ?? 'N/A') ?></td>
                                    <td>
                                        <small class="text-muted"><?= $order['oldAvailableUnits'] ?? '-' ?></small>
                                        &rarr;
                                        <strong><?= $order['newAvailableUnits'] ?? '-' ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($order['price'] ?? '0') ?> <?= htmlspecialchars($order['currency'] ?? '') ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($order['comment'] ?? '') ?></small></td>
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