@extends('backend.layouts.master')

@section('title', 'Gestion des Webhooks')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Webhooks</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">
                        <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Paramètres</li>
                    <li class="breadcrumb-item active">Webhooks</li>
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
                        <div>
                            <h5>Liste des Webhooks</h5>
                            <span>Configurez des webhooks pour recevoir des notifications d'événements</span>
                        </div>
                        <div>
                            <a href="<?= url('admin/settings/webhooks/create') ?>" class="btn btn-primary">
                                <i data-feather="plus"></i> Nouveau Webhook
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>URL</th>
                                    <th>Événements</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($webhooks)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="py-4">
                                            <i data-feather="inbox" style="width: 48px; height: 48px;"></i>
                                            <p class="mt-2">Aucun webhook configuré</p>
                                            <a href="<?= url('admin/settings/webhooks/create') ?>" class="btn btn-primary mt-2">
                                                <i data-feather="plus"></i> Créer un webhook
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($webhooks as $webhook): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($webhook->name) ?></strong></td>
                                        <td>
                                            <code><?= htmlspecialchars($webhook->url) ?></code>
                                        </td>
                                        <td>
                                            <?php
                                            $events = is_string($webhook->events) ? json_decode($webhook->events, true) : $webhook->events;
                                            if (is_array($events)):
                                                foreach (array_slice($events, 0, 3) as $event):
                                            ?>
                                                <span class="badge badge-secondary"><?= htmlspecialchars($event) ?></span>
                                            <?php
                                                endforeach;
                                                if (count($events) > 3):
                                            ?>
                                                <span class="badge badge-light">+<?= count($events) - 3 ?></span>
                                            <?php
                                                endif;
                                            endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($webhook->is_active): ?>
                                                <span class="badge badge-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info test-webhook"
                                                    data-id="<?= $webhook->id ?>" title="Tester">
                                                <i data-feather="zap"></i>
                                            </button>
                                            <a href="<?= url('admin/settings/webhooks/' . $webhook->id . '/logs') ?>"
                                               class="btn btn-sm btn-secondary" title="Logs">
                                                <i data-feather="file-text"></i>
                                            </a>
                                            <a href="<?= url('admin/settings/webhooks/' . $webhook->id . '/edit') ?>"
                                               class="btn btn-sm btn-warning" title="Modifier">
                                                <i data-feather="edit-2"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-webhook"
                                                    data-id="<?= $webhook->id ?>" title="Supprimer">
                                                <i data-feather="trash-2"></i>
                                            </button>
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
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Test webhook
    document.querySelectorAll('.test-webhook').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const button = this;
            button.disabled = true;

            fetch('<?= url('admin/settings/webhooks') ?>/' + id + '/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success ? 'Test réussi! Code HTTP: ' + data.http_code : 'Test échoué: ' + data.error);
                button.disabled = false;
            });
        });
    });

    // Delete webhook
    document.querySelectorAll('.delete-webhook').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce webhook ?')) {
                const id = this.dataset.id;
                fetch('<?= url('admin/settings/webhooks') ?>/' + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                }).then(() => location.reload());
            }
        });
    });
</script>
@endsection
