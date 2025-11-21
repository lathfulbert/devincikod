@extends('admin.layout')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Permissions']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<!-- Actions -->
<div class="row mb-3">
    <div class="col-12 text-end">
        <a href="<?= url('/admin/permissions/create') ?>" class="btn btn-primary">
            <i data-feather="plus"></i> Nouvelle permission
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php
        $card_title = "Liste des Permissions";
        component('card-start');
        ?>

        <table id="permissionsTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Module</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($permissions)): ?>
                    <?php foreach ($permissions as $permission): ?>
                        <tr>
                            <td><?= $permission->id ?></td>
                            <td><strong><?= htmlspecialchars($permission->name ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($permission->description ?? '') ?: '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <?php if (!empty($permission->module_name)): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($permission->module_name) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/admin/permissions/' . $permission->id . '/edit') ?>"
                                    class="btn btn-sm btn-warning" title="Éditer">
                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                </a>
                                <form action="<?= url('/admin/permissions/' . $permission->id . '/delete') ?>"
                                    method="POST" style="display:inline;"
                                    onsubmit="return confirm('Supprimer cette permission ?');">
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
                            <i data-feather="inbox"></i> Aucune permission trouvée
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $card_footer = "Total : " . count($permissions ?? []) . " permission(s)";
        component('card-end');
        ?>
    </div>
</div>

@endsection

@section('scripts')
<?php
$datatable_id = 'permissionsTable';
component('datatable-init');
?>
<script>
    feather.replace();
</script>
@endsection