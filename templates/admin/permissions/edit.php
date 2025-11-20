<?php ob_start(); ?>

<div class="header">
    <h1>Edit Permission: <?= htmlspecialchars($permission->name) ?></h1>
    <a href="<?= url('/admin/permissions') ?>" class="btn btn-primary">Back</a>
</div>

<div class="card">
    <form action="<?= url('/admin/permissions/' . $permission->id) ?>" method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($permission->name) ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($permission->slug) ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Update Permission</button>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
