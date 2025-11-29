@extends('backend.layouts.master')

@section('title', 'Campaign Details')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= htmlspecialchars($campaign->name) ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms/campaigns') ?>">Campaigns</a></li>
                    <li class="breadcrumb-item active">Détails</li>
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
        <!-- Campaign Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Résumé de la Campagne</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
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
                        </dd>

                        <dt class="col-sm-6">Total:</dt>
                        <dd class="col-sm-6"><?= $campaign->total_recipients ?></dd>

                        <dt class="col-sm-6">Envoyés:</dt>
                        <dd class="col-sm-6 text-success"><strong><?= $campaign->sent_count ?></strong></dd>

                        <dt class="col-sm-6">Échoués:</dt>
                        <dd class="col-sm-6 text-danger"><strong><?= $campaign->failed_count ?></strong></dd>

                        <dt class="col-sm-6">Progression:</dt>
                        <dd class="col-sm-6">
                            <?php $progress = $campaign->getProgress(); ?>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%">
                                    <?= $progress ?>%
                                </div>
                            </div>
                        </dd>

                        <dt class="col-sm-12 mt-3">Message:</dt>
                        <dd class="col-sm-12">
                            <div class="alert alert-light">
                                <?= nl2br(htmlspecialchars($campaign->message)) ?>
                            </div>
                        </dd>

                        <dt class="col-sm-6">Sender ID:</dt>
                        <dd class="col-sm-6"><?= htmlspecialchars($campaign->sender_id) ?></dd>

                        <dt class="col-sm-6">Créé le:</dt>
                        <dd class="col-sm-6"><?= date('d/m/Y H:i', strtotime($campaign->created_at)) ?></dd>

                        <?php if ($campaign->scheduled_at): ?>
                            <dt class="col-sm-6">Programmé:</dt>
                            <dd class="col-sm-6"><?= date('d/m/Y H:i', strtotime($campaign->scheduled_at)) ?></dd>
                        <?php endif; ?>

                        <?php if ($campaign->started_at): ?>
                            <dt class="col-sm-6">Démarré:</dt>
                            <dd class="col-sm-6"><?= date('d/m/Y H:i', strtotime($campaign->started_at)) ?></dd>
                        <?php endif; ?>

                        <?php if ($campaign->completed_at): ?>
                            <dt class="col-sm-6">Terminé:</dt>
                            <dd class="col-sm-6"><?= date('d/m/Y H:i', strtotime($campaign->completed_at)) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Queue Items -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>File d'attente (<?= count($queueItems) ?> SMS)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Destinataire</th>
                                    <th>Status</th>
                                    <th>Tentatives</th>
                                    <th>Date envoi</th>
                                    <th>Erreur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($queueItems)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun SMS en queue</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($queueItems as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item->recipient) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($item->status) {
                                                    'pending' => 'secondary',
                                                    'processing' => 'warning',
                                                    'sent' => 'success',
                                                    'failed' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($item->status) ?></span>
                                            </td>
                                            <td><?= $item->attempts ?></td>
                                            <td><?= $item->sent_at ? date('d/m/Y H:i', strtotime($item->sent_at)) : '-' ?></td>
                                            <td>
                                                <?php if ($item->error_message): ?>
                                                    <small class="text-danger"><?= htmlspecialchars($item->error_message) ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
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

    // Auto-refresh every 10 seconds if campaign is sending
    <?php if ($campaign->status === 'sending'): ?>
    setTimeout(function() {
        location.reload();
    }, 10000);
    <?php endif; ?>
</script>
@endsection
