@extends('admin.layout')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Modules']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<div class="row">
    <div class="col-12">
        <?php
        $card_title = "Gestion des Modules";
        component('card-start');
        ?>

        <table id="modulesTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Version</th>
                    <th>Description</th>
                    <th>Auteur</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($modules)): ?>
                    <?php foreach ($modules as $module): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($module['name']) ?></strong></td>
                            <td><span class="badge bg-light text-dark"><?= htmlspecialchars($module['version']) ?></span></td>
                            <td><?= htmlspecialchars($module['description']) ?: '<span class="text-muted">-</span>' ?></td>
                            <td><?= htmlspecialchars($module['author']) ?: '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <?php if ($module['is_installed']): ?>
                                    <?php if ($module['is_enabled']): ?>
                                        <span class="badge bg-success"><i data-feather="check-circle" style="width: 12px; height: 12px;"></i> Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i data-feather="pause-circle" style="width: 12px; height: 12px;"></i> Désactivé</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i data-feather="download" style="width: 12px; height: 12px;"></i> Non installé</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!$module['is_installed']): ?>
                                    <form action="<?= url('/admin/modules/install') ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                        <button type="submit" class="btn btn-sm btn-primary" title="Installer">
                                            <i data-feather="download" style="width: 14px; height: 14px;"></i> Installer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <?php if ($module['is_enabled']): ?>
                                        <form action="<?= url('/admin/modules/disable') ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Désactiver">
                                                <i data-feather="pause" style="width: 14px; height: 14px;"></i> Désactiver
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= url('/admin/modules/enable') ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Activer">
                                                <i data-feather="play" style="width: 14px; height: 14px;"></i> Activer
                                            </button>
                                        </form>

                                        <form action="<?= url('/admin/modules/uninstall') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir désinstaller ce module ? Cela supprimera ses données.');">
                                            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="name" value="<?= $module['name'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Désinstaller">
                                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i data-feather="inbox"></i> Aucun module trouvé
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $card_footer = "Total : " . count($modules ?? []) . " module(s)";
        component('card-end');
        ?>
    </div>
</div>

@endsection

@section('scripts')
<?php
$datatable_id = 'modulesTable';
component('datatable-init');
?>
<script>
    feather.replace();
</script>
@endsection