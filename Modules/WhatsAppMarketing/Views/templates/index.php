@extends('backend.layouts.master')

@section('title', $title)

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'WhatsApp', 'url' => '/admin/whatsapp'],
        ['label' => 'Templates']
    ];
    component('breadcrumb');
    ?>

    <?php component('alerts'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Modèles de Messages (Templates)</h6>
            <div>
                <!-- Example sync button for first gateway found -->
                <?php $gateway = Modules\WhatsAppMarketing\Models\WhatsAppGateway::getDefault(); ?>
                <?php if ($gateway): ?>
                    <a href="<?= url('/admin/whatsapp/templates/' . $gateway->id . '/sync') ?>" class="btn btn-info btn-sm">
                        <i data-feather="refresh-cw"></i> Sync depuis <?= $gateway->provider ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                Pour utiliser WhatsApp Business API, vous devez créer vos templates sur la plateforme de votre fournisseur (ex: Meta Business Manager, Twilio Console) et les synchroniser ici.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Langue</th>
                            <th>Statut</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td><?= htmlspecialchars($template->name) ?></td>
                                <td><?= htmlspecialchars($template->category) ?></td>
                                <td><?= htmlspecialchars($template->language) ?></td>
                                <td>
                                    <?php if ($template->status === 'APPROVED'): ?>
                                        <span class="badge bg-success">Approuvé</span>
                                    <?php elseif ($template->status === 'REJECTED'): ?>
                                        <span class="badge bg-danger">Rejeté</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><?= $template->status ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= substr(htmlspecialchars($template->components), 0, 50) ?>...</small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucun template synchronisé.</td>
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