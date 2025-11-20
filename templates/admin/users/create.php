@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Create User</h1>
    <a href="<?= url('/admin/users') ?>" class="btn btn-primary">Back</a>
</div>

<div class="card">
    <form action="<?= url('/admin/users') ?>" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Roles</label>
            <div class="checkbox-group">
                <?php foreach ($roles as $role): ?>
                    <label>
                        <input type="checkbox" name="roles[]" value="<?= $role->id ?>">
                        <?= htmlspecialchars($role->name) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Create User</button>
    </form>
</div>
@endsection