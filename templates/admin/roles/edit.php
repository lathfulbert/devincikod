@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Edit Role: <?= htmlspecialchars($role->name) ?></h1>
    <a href="<?= url('/admin/roles') ?>" class="btn btn-primary">Back</a>
</div>

<div class="card">
    <form action="<?= url('/admin/roles/' . $role->id . '/update') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($role->name) ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($role->slug) ?>" required>
        </div>
        <div class="form-group">
            <label>Permissions</label>
            <div class="checkbox-group">
                <?php foreach ($permissions as $permission): ?>
                    <label>
                        <input type="checkbox" name="permissions[]" value="<?= $permission->id ?>" <?= in_array($permission->id, $rolePermissionIds) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($permission->name) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Update Role</button>
    </form>
</div>
@endsection