<div style="max-width: 400px; margin: 0 auto;">
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST" action="<?= url('/login') ?>">
        <div style="margin-bottom: 1rem;">
            <label>Username (admin):</label><br>
            <input type="text" name="username" style="width: 100%; padding: 0.5rem;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label>Password (password):</label><br>
            <input type="password" name="password" style="width: 100%; padding: 0.5rem;">
        </div>
        <button type="submit" style="padding: 0.5rem 1rem;">Login</button>
    </form>
</div>
