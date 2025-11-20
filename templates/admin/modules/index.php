@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Modules</h1>
    <a href="<?= url('/admin/modules/create') ?>" class="btn btn-success">Créer un Module</a>
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
                <th>Icône</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($modules as $module): ?>
                <tr>
                    <td><?= $module->id ?></td>
                    <td><?= escape($module->name) ?></td>
                    <td><code><?= escape($module->slug) ?></code></td>
                    <td><?= escape($module->icon ?? '-') ?></td>
                    <td><?= escape(str_limit($module->description ?? '', 50)) ?></td>
                    <td>
                        <span class="badge <?= $module->is_active ? 'badge-success' : 'badge-secondary' ?>">
                            <?= $module->is_active ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= url('/admin/modules/' . $module->id . '/edit') ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <form action="<?= url('/admin/modules/' . $module->id . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr ?');">
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