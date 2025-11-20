<?php ob_start(); ?>

<div class="header">
    <h1>Users</h1>
    <a href="<?= url('/admin/users/create') ?>" class="btn btn-success">Create User</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Roles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id ?></td>
                <td><?= htmlspecialchars($user->username) ?></td>
                <td>
                    <?php 
                    $roles = $user->roles();
                    $roleNames = array_map(fn($r) => $r->name, $roles);
                    echo implode(', ', $roleNames);
                    ?>
                </td>
                <td>
                    <a href="<?= url('/admin/users/' . $user->id . '/edit') ?>" class="btn btn-primary btn-sm">Edit</a>
                    <form action="<?= url('/admin/users/' . $user->id . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
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
