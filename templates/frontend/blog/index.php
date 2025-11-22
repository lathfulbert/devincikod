<h2>Latest Blog Posts</h2>

<?php foreach ($posts as $post): ?>
    <article style="margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
        <h3><?= htmlspecialchars($post['title']) ?></h3>
        <p><?= htmlspecialchars($post['content']) ?></p>
    </article>
<?php endforeach; ?>
