@extends('backend.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        </div>
    </div>

    <div class="row">
        <?php foreach ($gateways as $gateway): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    <?= htmlspecialchars($gateway->name) ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $gateway->is_active ? '<span class="text-success">Actif</span>' : '<span class="text-secondary">Inactif</span>' ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-server fa-2x text-gray-300"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <?php if ($gateway->provider_code === 'orange_ci'): ?>
                                <a href="<?= url('/admin/sms/providers/orange') ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-chart-line"></i> Voir Statistiques
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>En développement</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
@endsection