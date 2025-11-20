<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SunuFramework' ?></title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        header { background: #333; color: #fff; padding: 1rem; }
        main { padding: 2rem; }
        footer { background: #ddd; padding: 1rem; text-align: center; position: fixed; bottom: 0; width: 100%; }
    </style>
</head>
<body>
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <h1>SunuFramework Core</h1>
        <nav>
            <a href="<?= url('/') ?>" style="color: #fff; margin-right: 15px; text-decoration: none;">Home</a>
            <a href="<?= url('/login') ?>" style="color: #fff; margin-right: 15px; text-decoration: none;">Login</a>
            <a href="<?= url('/admin/dashboard') ?>" style="color: #fff; text-decoration: none;">Admin</a>
        </nav>
    </header>
    <main>
        <?= $content ?>
    </main>
    <footer>
        &copy; <?= date('Y') ?> SunuFramework
    </footer>
</body>
</html>
