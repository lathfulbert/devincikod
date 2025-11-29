@extends('backend.layouts.master')

@section('title', 'SMS Campaigns')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'SMS Campaigns' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item active">Campaigns</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Liste des Campagnes SMS</h5>
                    <a href="<?= url('/admin/sms/bulk') ?>" class="btn btn-primary">
                        <i data-feather="plus"></i> Nouvelle Campagne
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Status</th>
                                    <th>Destinataires</th>
                                    <th>Envoyés</th>
                                    <th>Échoués</th>
                                    <th>Progression</th>
                                    <th>Date création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($campaigns)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Aucune campagne trouvée.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($campaigns as $campaign): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($campaign->name) ?></strong></td>
                                            <td>
                                                <?php
                                                $statusClass = match($campaign->status) {
                                                    'draft' => 'secondary',
                                                    'scheduled' => 'info',
                                                    'sending' => 'warning',
                                                    'completed' => 'success',
                                                    'failed' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($campaign->status) ?></span>
                                            </td>
                                            <td><?= $campaign->total_recipients ?></td>
                                            <td><?= $campaign->sent_count ?></td>
                                            <td><?= $campaign->failed_count ?></td>
                                            <td>
                                                <?php $progress = $campaign->getProgress(); ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%">
                                                        <?= $progress ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($campaign->created_at)) ?></td>
                                            <td>
                                                <a href="<?= url('/admin/sms/campaigns/' . $campaign->id) ?>" class="btn btn-sm btn-info" title="Voir détails">
                                                    <i data-feather="eye"></i>
                                                </a>
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

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection
