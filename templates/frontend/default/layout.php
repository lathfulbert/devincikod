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
    <header>
        <h1>SunuFramework Core</h1>
    </header>
    <main>
        <?= $content ?>
    </main>
    <footer>
        &copy; <?= date('Y') ?> SunuFramework
    </footer>
</body>
</html>
