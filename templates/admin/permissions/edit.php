@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Modifier la Permission : <?= escape($permission->name) ?></h1>
    <a href="<?= url('/admin/permissions') ?>" class="btn btn-primary">Retour</a>
</div>

<?php if (flash('error')): ?>
    <div class="alert alert-danger"><?= flash('error') ?></div>
<?php endif; ?>

<div class="card">
    <form action="<?= url('/admin/permissions/' . $permission->id) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" id="name" name="name" value="<?= escape($permission->name) ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" id="slug" name="slug" value="<?= escape($permission->slug) ?>" required>
            <small>Caractères alphanumériques uniquement (ex: users.create)</small>
        </div>
        <div class="form-group">
            <label for="module_id">Module</label>
            <select id="module_id" name="module_id">
                <option value="">-- Aucun module --</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module->id ?>" <?= $permission->module_id == $module->id ? 'selected' : '' ?>>
                        <?= escape($module->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= escape($permission->description ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Mettre à jour la Permission</button>
    </form>
</div>
@endsection