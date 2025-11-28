@extends('backend.layouts.master')

@section('title', 'Configuration SMS')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/settings') ?>">Settings</a></li>
                    <li class="breadcrumb-item active">SMS</li>
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
        <!-- Gateways List -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i data-feather="server"></i> Gateways SMS</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="<?= url('/admin/settings/sms/gateways/create') ?>" class="btn btn-sm btn-primary">
                                <i data-feather="plus"></i> Ajouter Gateway
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
                                    <th>Provider</th>
                                    <th>Sender ID</th>
                                    <th>Priorité</th>
                                    <th>Statut</th>
                                    <th>Par défaut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gateways as $gateway): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($gateway->name) ?></strong></td>
                                        <td><code><?= htmlspecialchars($gateway->provider_code) ?></code></td>
                                        <td><?= htmlspecialchars($gateway->sender_id ?? '-') ?></td>
                                        <td><?= $gateway->priority ?></td>
                                        <td>
                                            <?php if ($gateway->is_active): ?>
                                                <span class="badge badge-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($gateway->is_default): ?>
                                                <span class="badge badge-primary">Défaut</span>
                                            <?php else: ?>
                                                <a href="<?= url('/admin/settings/sms/gateways/' . $gateway->id . '/set-default') ?>"
                                                    class="btn btn-sm btn-outline-primary">
                                                    Définir
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button onclick="testGateway(<?= $gateway->id ?>)"
                                                    class="btn btn-info" title="Tester">
                                                    <i data-feather="activity"></i>
                                                </button>
                                                <a href="<?= url('/admin/settings/sms/gateways/' . $gateway->id . '/edit') ?>"
                                                    class="btn btn-warning" title="Éditer">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                <a href="<?= url('/admin/settings/sms/gateways/' . $gateway->id . '/toggle') ?>"
                                                    class="btn btn-secondary" title="Activer/Désactiver">
                                                    <i data-feather="power"></i>
                                                </a>
                                                <form method="POST" action="<?= url('/admin/settings/sms/gateways/' . $gateway->id . '/delete') ?>"
                                                    style="display:inline;" onsubmit="return confirm('Supprimer ce gateway ?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-danger" title="Supprimer">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($gateways)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Aucun gateway configuré. <a href="<?= url('/admin/settings/sms/gateways/create') ?>">Ajouter un gateway</a>
                                        </td>
                                    </tr>
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
    function testGateway(id) {
        fetch(`<?= url('/admin/settings/sms/gateways/') ?>/${id}/test`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Test réussi: ' + data.message);
                } else {
                    alert('✗ Test échoué: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erreur lors du test: ' + error);
            });
    }

    feather.replace();
</script>
@endsection