@extends('backend.layouts.master')

@section('title', $title)

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'WhatsApp', 'url' => '/admin/whatsapp'],
        ['label' => 'Passerelles']
    ];
    component('breadcrumb');
    ?>

    <?php component('alerts'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Configuration des Passerelles</h6>
            <a href="<?= url('/admin/whatsapp/gateways/create') ?>" class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Ajouter une passerelle
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Fournisseur</th>
                            <th>Numéro</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gateways as $gateway): ?>
                            <tr>
                                <td><?= htmlspecialchars($gateway->name) ?></td>
                                <td><?= ucfirst($gateway->provider) ?></td>
                                <td><?= htmlspecialchars($gateway->phone_number ?? '-') ?></td>
                                <td>
                                    <?php if ($gateway->is_active): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="<?= url('/admin/whatsapp/gateways/' . $gateway->id . '/delete') ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer la suppression ?')">
                                            <i data-feather="trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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