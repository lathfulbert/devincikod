<?php

/**
 * Vue des statistiques du cache
 * @var array $stats Statistiques du cache
 */
?>

<?php
// Afficher les messages flash
if (isset($_SESSION['flash'])) {
    foreach ($_SESSION['flash'] as $type => $message) {
        echo "<div style='padding: 10px; margin-bottom: 15px; border-radius: 4px; background: " . ($type === 'success' ? '#d4edda' : '#f8d7da') . "; color: " . ($type === 'success' ? '#155724' : '#721c24') . ";'>{$message}</div>";
    }
    unset($_SESSION['flash']);
}
?>

<div class="header">
    <h1><?= $title ?? 'Statistiques du Cache' ?></h1>
    <div>
        <a href="<?= url('/admin/cache') ?>" class="btn btn-primary">Configuration</a>
        <form method="POST" action="<?= url('/admin/cache/clear') ?>" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir vider tout le cache ?');">
            <button type="submit" class="btn btn-danger">Vider le cache</button>
        </form>
    </div>
</div>

<!-- Cartes de statistiques -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">

    <div class="card">
        <h3>Driver actuel</h3>
        <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
            <?= ucfirst($stats['driver'] ?? 'unknown') ?>
        </p>
        <p style="color: <?= ($stats['enabled'] ?? false) ? 'green' : 'red' ?>;">
            <?= ($stats['enabled'] ?? false) ? '✓ Activé' : '✗ Désactivé' ?>
        </p>
    </div>

    <?php if (isset($stats['total_files'])): ?>
        <!-- Stats Filesystem -->
        <div class="card">
            <h3>Fichiers en cache</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['total_files'] ?>
            </p>
            <p>fichiers</p>
        </div>

        <div class="card">
            <h3>Taille totale</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['total_size_readable'] ?? '0 B' ?>
            </p>
            <p>sur disque</p>
        </div>
    <?php endif; ?>

    <?php if (isset($stats['total_keys'])): ?>
        <!-- Stats Redis -->
        <div class="card">
            <h3>Clés en cache</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['total_keys'] ?>
            </p>
            <p>clés</p>
        </div>

        <div class="card">
            <h3>Mémoire utilisée</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['used_memory'] ?? '0' ?>
            </p>
            <p>Redis v<?= $stats['redis_version'] ?? 'unknown' ?></p>
        </div>

        <div class="card">
            <h3>Clients connectés</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['connected_clients'] ?? 0 ?>
            </p>
            <p>connexions actives</p>
        </div>
    <?php endif; ?>

    <?php if (isset($stats['total_items'])): ?>
        <!-- Stats Memcached -->
        <div class="card">
            <h3>Items en cache</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['total_items'] ?>
            </p>
            <p>sur <?= $stats['servers'] ?? 1 ?> serveur(s)</p>
        </div>

        <div class="card">
            <h3>Mémoire utilisée</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['total_size_readable'] ?? '0 B' ?>
            </p>
            <p>Memcached</p>
        </div>
    <?php endif; ?>

    <?php if (isset($stats['num_entries'])): ?>
        <!-- Stats APCu -->
        <div class="card">
            <h3>Entrées en cache</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['num_entries'] ?>
            </p>
            <p>entrées</p>
        </div>

        <div class="card">
            <h3>Mémoire utilisée</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['memory_used_readable'] ?? '0 B' ?>
            </p>
            <p>APCu</p>
        </div>

        <div class="card">
            <h3>Taux de hit</h3>
            <p style="font-size: 2em; font-weight: bold; margin: 10px 0;">
                <?= $stats['hit_rate'] ?? '0%' ?>
            </p>
            <p>efficacité du cache</p>
        </div>
    <?php endif; ?>

</div>

<!-- Informations détaillées -->
<div class="card">
    <h2>Informations détaillées</h2>
    <table>
        <thead>
            <tr>
                <th>Propriété</th>
                <th>Valeur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats as $key => $value): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($key) ?></strong></td>
                    <td><?= is_array($value) ? json_encode($value) : htmlspecialchars((string)$value) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Préfixe actuel</h2>
    <p>Toutes les clés de cache sont préfixées par : <code style="background: #f4f4f4; padding: 2px 5px; border-radius: 3px;"><?= htmlspecialchars($stats['prefix'] ?? 'aucun') ?></code></p>
    <p><small>Ceci permet d'éviter les collisions avec d'autres applications utilisant le même stockage.</small></p>
</div>

<script>
    // Auto-refresh toutes les 5 secondes (optionnel)
    // setTimeout(() => location.reload(), 5000);
</script>