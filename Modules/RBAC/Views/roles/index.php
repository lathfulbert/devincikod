@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Rôles']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <!--Actions-->
    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="<?= route('admin.roles.create') ?>" class="btn btn-primary">
                <i data-feather="plus"></i> Nouveau rôle
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php
            component('card-start', ['card_title' => "Liste des Rôles"]);
            ?>

            <table id="rolesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><?= $role->id ?></td>
                                <td><strong><?= htmlspecialchars($role->name ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($role->description ?? '') ?: '<span class="text-muted">-</span>' ?></td>
                                <td>
                                    <?php
                                    $permissions = $role->permissions()->getResults();
                                    if (!empty($permissions)) {
                                        echo '<span class="badge bg-info">' . count($permissions) . ' permission(s)</span>';
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= route('admin.roles.edit', ['id' => $role->id]) ?>"
                                        class="btn btn-sm btn-warning" title="Éditer">
                                        <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                    </a>
                                    <form action="<?= route('admin.roles.delete', ['id' => $role->id]) ?>"
                                        method="POST" style="display:inline;"
                                        onsubmit="return confirm('Supprimer ce rôle ?');">
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
                                <i data-feather="inbox"></i> Aucun rôle trouvé
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            $card_footer = "Total : " . count($roles ?? []) . " rôle(s)";
            component('card-end');
            ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<?php
$datatable_id = 'rolesTable';
component('datatable-init');
?>
<script>
    feather.replace();
</script>
@endsection