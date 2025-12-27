@extends('backend.layouts.master')

@section('title', $title)

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'WhatsApp', 'url' => '/admin/whatsapp'],
        ['label' => 'Campagnes']
    ];
    component('breadcrumb');
    ?>

    <?php component('alerts'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Campagnes Marketing</h6>
            <a href="<?= url('/admin/whatsapp/campaigns/create') ?>" class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Créer une campagne
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Template</th>
                            <th>Statut</th>
                            <th>Planifié pour</th>
                            <th>Envoyés</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $campaign): ?>
                            <tr>
                                <td><?= htmlspecialchars($campaign->name) ?></td>
                                <td><?= htmlspecialchars($campaign->template->name ?? '-') ?></td>
                                <td>
                                    <?php if ($campaign->status === 'completed'): ?>
                                        <span class="badge bg-success">Terminé</span>
                                    <?php elseif ($campaign->status === 'processing'): ?>
                                        <span class="badge bg-info">En cours</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst($campaign->status) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $campaign->scheduled_at ?? '-' ?></td>
                                <td><?= $campaign->total_sent ?></td>
                                <td>
                                    <a href="#" class="btn btn-info btn-sm">
                                        <i data-feather="eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($campaigns)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune campagne créée.</td>
                            </tr>
                        <?php endif; ?>
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