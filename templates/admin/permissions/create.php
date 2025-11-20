@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Créer une Permission</h1>
    <a href="<?= url('/admin/permissions') ?>" class="btn btn-primary">Retour</a>
</div>

<?php if (flash('error')): ?>
    <div class="alert alert-danger"><?= flash('error') ?></div>
<?php endif; ?>

<div class="card">
    <form action="<?= url('/admin/permissions') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" id="slug" name="slug" value="<?= old('slug') ?>" required>
            <small>Caractères alphanumériques uniquement (ex: users.create)</small>
        </div>
        <div class="form-group">
            <label for="module_id">Module</label>
            <select id="module_id" name="module_id">
                <option value="">-- Aucun module --</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module->id ?>" <?= old('module_id') == $module->id ? 'selected' : '' ?>>
                        <?= escape($module->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= old('description') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Créer la Permission</button>
    </form>
</div>
@endsection