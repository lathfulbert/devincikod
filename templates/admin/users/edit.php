@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Edit User: <?= htmlspecialchars($user->username) ?></h1>
    <a href="<?= url('/admin/users') ?>" class="btn btn-primary">Back</a>
</div>

<div class="card">
    <form action="<?= url('/admin/users/' . $user->id) ?>" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user->username) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password (Leave blank to keep current)</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label>Roles</label>
            <div class="checkbox-group">
                <?php foreach ($roles as $role): ?>
                    <label>
                        <input type="checkbox" name="roles[]" value="<?= $role->id ?>" <?= in_array($role->id, $userRoleIds) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($role->name) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Update User</button>
    </form>
</div>
@endsection