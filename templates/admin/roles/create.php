@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Create Role</h1>
    <a href="<?= url('/admin/roles') ?>" class="btn btn-primary">Back</a>
</div>

<div class="card">
    <form action="<?= url('/admin/roles') ?>" method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" required>
        </div>
        <div class="form-group">
            <label>Permissions</label>
            <div class="checkbox-group">
                <?php foreach ($permissions as $permission): ?>
                    <label>
                        <input type="checkbox" name="permissions[]" value="<?= $permission->id ?>">
                        <?= htmlspecialchars($permission->name) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Create Role</button>
    </form>
</div>
@endsection