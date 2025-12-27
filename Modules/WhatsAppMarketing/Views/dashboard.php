@extends('backend.layouts.master')

@section('title', $title)

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'WhatsApp Marketing']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Messages Envoyés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_sent'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="send" class="text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Messages Livrés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_delivered'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="check-circle" class="text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Messages Lus</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_read'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="eye" class="text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Coût Estimé</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_cost'], 2) ?> €</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="dollar-sign" class="text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Campagnes Récentes</h6>
                    <a href="<?= url('/admin/whatsapp/campaigns') ?>" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php if (count($recentCampaigns ?? []) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Stats (Env/Liv/Lu)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentCampaigns as $campaign): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($campaign->name) ?></td>
                                            <td><span class="badge bg-secondary"><?= $campaign->status ?></span></td>
                                            <td><?= $campaign->scheduled_at ?? $campaign->created_at ?></td>
                                            <td>
                                                <?= $campaign->total_sent ?> /
                                                <?= $campaign->total_delivered ?> /
                                                <?= $campaign->total_read ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Aucune campagne récente.</p>
                        <div class="text-center">
                            <a href="<?= url('/admin/whatsapp/campaigns/create') ?>" class="btn btn-primary btn-sm">
                                <i data-feather="plus"></i> Créer une campagne
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Passerelles Actives</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($gateways as $gateway): ?>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <strong><?= htmlspecialchars($gateway->name) ?></strong>
                                <br>
                                <small class="text-muted"><?= ucfirst($gateway->provider) ?></small>
                            </div>
                            <div>
                                <?php if ($gateway->is_active): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactif</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <a href="<?= url('/admin/whatsapp/gateways') ?>" class="btn btn-light btn-block w-100">Gérer les passerelles</a>
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