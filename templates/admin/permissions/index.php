@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Permissions</h1>
    <a href="<?= url('/admin/permissions/create') ?>" class="btn btn-success">Créer une Permission</a>
</div>

<?php if (flash('success')): ?>
    <div class="alert alert-success"><?= flash('success') ?></div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Slug</th>
                <th>Module</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permissions as $permission): ?>
                <tr>
                    <td><?= $permission->id ?></td>
                    <td><?= escape($permission->name) ?></td>
                    <td><code><?= escape($permission->slug) ?></code></td>
                    <td>
                        <?php
                        $module = $permission->module();
                        echo $module ? escape($module->name) : '<span class="text-muted">-</span>';
                        ?>
                    </td>
                    <td><?= escape(str_limit($permission->description ?? '', 40)) ?></td>
                    <td>
                        <a href="<?= url('/admin/permissions/' . $permission->id . '/edit') ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <form action="<?= url('/admin/permissions/' . $permission->id . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr ?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
@endsection