@extends('backend.layouts.master')

@section('title', 'Historique des Traductions')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Historique des Traductions</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">
                        <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Paramètres</li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/settings/translations') ?>">Traductions</a></li>
                    <li class="breadcrumb-item active">Historique</li>
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
                            <h5>Historique des Modifications</h5>
                            <span>Consultez toutes les modifications apportées aux traductions</span>
                        </div>
                        <div>
                            <a href="<?= url('admin/settings/translations') ?>" class="btn btn-light">
                                <i data-feather="arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Traduction</th>
                                    <th>Ancienne Valeur</th>
                                    <th>Nouvelle Valeur</th>
                                    <th>Modifié par</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="py-4">
                                            <i data-feather="inbox" style="width: 48px; height: 48px;"></i>
                                            <p class="mt-2">Aucun historique disponible</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($history as $entry): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-light">
                                                <?= date('d/m/Y H:i', strtotime($entry['created_at'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($entry['translation_id']) ?></code>
                                        </td>
                                        <td>
                                            <div class="text-muted" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($entry['old_value']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($entry['new_value']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                User #<?= htmlspecialchars($entry['changed_by']) ?>
                                            </span>
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
</script>
@endsection
