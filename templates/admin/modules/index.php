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

<!-- Actions -->
<div class="row mb-3">
    <div class="col-12 text-end">
        <a href="<?= url('/admin/modules/create') ?>" class="btn btn-primary">
            <i data-feather="plus"></i> Nouveau module
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php
        $card_title = "Liste des Modules";
        component('card-start');
        ?>

        <table id="modulesTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($modules)): ?>
                    <?php foreach ($modules as $module): ?>
                        <tr>
                            <td><?= $module->id ?></td>
                            <td><strong><?= htmlspecialchars($module->name ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($module->description ?? '') ?: '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <?php if (!empty($module->is_active)): ?>
                                    <span class="badge bg-success"><i data-feather="check-circle" style="width: 12px; height: 12px;"></i> Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i data-feather="x-circle" style="width: 12px; height: 12px;"></i> Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/admin/modules/' . $module->id . '/edit') ?>"
                                    class="btn btn-sm btn-warning" title="Éditer">
                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                </a>
                                <form action="<?= url('/admin/modules/' . $module->id . '/delete') ?>"
                                    method="POST" style="display:inline;"
                                    onsubmit="return confirm('Supprimer ce module ?');">
                                    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
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