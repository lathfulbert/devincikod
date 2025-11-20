<?php ob_start(); ?>

<div class="header">
    <h1>Roles</h1>
    <a href="<?= url('/admin/roles/create') ?>" class="btn btn-success">Create Role</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Permissions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
            <tr>
                <td><?= $role->id ?></td>
                <td><?= htmlspecialchars($role->name) ?></td>
                <td><?= htmlspecialchars($role->slug) ?></td>
                <td>
                    <?php 
                    $permissions = $role->permissions();
                    $permNames = array_map(fn($p) => $p->name, $permissions);
                    echo implode(', ', $permNames);
                    ?>
                </td>
                <td>
                    <a href="<?= url('/admin/roles/' . $role->id . '/edit') ?>" class="btn btn-primary btn-sm">Edit</a>
                    <form action="<?= url('/admin/roles/' . $role->id . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
