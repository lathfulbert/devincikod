@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Permissions</h1>
    <a href="<?= url('/admin/permissions/create') ?>" class="btn btn-success">Create Permission</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permissions as $permission): ?>
                <tr>
                    <td><?= $permission->id ?></td>
                    <td><?= htmlspecialchars($permission->name) ?></td>
                    <td><?= htmlspecialchars($permission->slug) ?></td>
                    <td>
                        <a href="<?= url('/admin/permissions/' . $permission->id . '/edit') ?>" class="btn btn-primary btn-sm">Edit</a>
                        <form action="<?= url('/admin/permissions/' . $permission->id . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
@endsection