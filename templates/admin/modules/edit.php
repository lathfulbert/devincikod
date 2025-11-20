@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Modifier le Module : <?= escape($module->name) ?></h1>
    <a href="<?= url('/admin/modules') ?>" class="btn btn-primary">Retour</a>
</div>

<?php if (flash('error')): ?>
    <div class="alert alert-danger"><?= flash('error') ?></div>
<?php endif; ?>

<div class="card">
    <form action="<?= url('/admin/modules/' . $module->id) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" id="name" name="name" value="<?= escape($module->name) ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" id="slug" name="slug" value="<?= escape($module->slug) ?>" required>
            <small>Caractères alphanumériques uniquement (ex: users-management)</small>
        </div>
        <div class="form-group">
            <label for="icon">Icône</label>
            <input type="text" id="icon" name="icon" value="<?= escape($module->icon ?? '') ?>">
            <small>Nom de l'icône (ex: users, settings, shield)</small>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?= escape($module->description ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= $module->is_active ? 'checked' : '' ?>>
                Module actif
            </label>
        </div>
        <button type="submit" class="btn btn-success">Mettre à jour le Module</button>
    </form>
</div>
@endsection