<h2>Dashboard</h2>
<p>Welcome, <?= htmlspecialchars($user['username']) ?>!</p>
<p>You are logged in.</p>
<a href="<?= url('/logout') ?>">Logout</a>
