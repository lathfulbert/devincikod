<?php

/**
 * Vue de configuration du cache
 * @var object $config Configuration actuelle
 * @var array $extensions Extensions disponibles
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
    <h1><?= $title ?? 'Configuration du Cache' ?></h1>
    <div>
        <a href="<?= url('/admin/cache/stats') ?>" class="btn btn-primary">Statistiques</a>
    </div>
</div>

<div class="card">
    <h2>Configuration Générale</h2>
    <form method="POST" action="<?= url('/admin/cache/update') ?>" id="cacheConfigForm">

        <!-- Configuration générale -->
        <div class="form-group">
            <label>
                <input type="checkbox" name="enabled" value="1" <?= $config->enabled ? 'checked' : '' ?>>
                Activer le système de cache
            </label>
        </div>

        <div class="form-group">
            <label for="driver">Driver de cache:</label>
            <select name="driver" id="driver" onchange="showDriverConfig(this.value)">
                <option value="filesystem" <?= $config->driver === 'filesystem' ? 'selected' : '' ?>>Filesystem</option>
                <option value="redis" <?= $config->driver === 'redis' ? 'selected' : '' ?> <?= !$extensions['redis'] ? 'disabled' : '' ?>>
                    Redis <?= !$extensions['redis'] ? '(Non disponible - installer Predis)' : '' ?>
                </option>
                <option value="memcached" <?= $config->driver === 'memcached' ? 'selected' : '' ?> <?= !$extensions['memcached'] ? 'disabled' : '' ?>>
                    Memcached <?= !$extensions['memcached'] ? '(Non disponible - installer extension)' : '' ?>
                </option>
                <option value="apcu" <?= $config->driver === 'apcu' ? 'selected' : '' ?> <?= !$extensions['apcu'] ? 'disabled' : '' ?>>
                    APCu <?= !$extensions['apcu'] ? '(Non disponible - installer extension)' : '' ?>
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="prefix">Préfixe des clés:</label>
            <input type="text" name="prefix" id="prefix" value="<?= htmlspecialchars($config->prefix) ?>">
            <small>Préfixe ajouté à toutes les clés de cache pour éviter les collisions</small>
        </div>

        <div class="form-group">
            <label for="default_ttl">TTL par défaut (secondes):</label>
            <input type="number" name="default_ttl" id="default_ttl" value="<?= $config->default_ttl ?>">
            <small>Durée de vie par défaut des éléments en cache (3600 = 1 heure)</small>
        </div>

        <!-- Configuration Filesystem -->
        <div id="config_filesystem" class="driver-config" style="display: <?= $config->driver === 'filesystem' ? 'block' : 'none' ?>">
            <h3>Configuration Filesystem</h3>
            <div class="form-group">
                <label for="filesystem_path">Chemin de stockage:</label>
                <input type="text" name="filesystem_path" id="filesystem_path" value="<?= htmlspecialchars($config->filesystem_path) ?>">
                <small>Chemin relatif à la racine du projet</small>
            </div>
            <button type="button" class="btn btn-success btn-sm" onclick="testDriver('filesystem')">Tester Filesystem</button>
            <span id="test_filesystem"></span>
        </div>

        <!-- Configuration Redis -->
        <div id="config_redis" class="driver-config" style="display: <?= $config->driver === 'redis' ? 'block' : 'none' ?>">
            <h3>Configuration Redis</h3>
            <div class="form-group">
                <label for="redis_host">Hôte:</label>
                <input type="text" name="redis_host" id="redis_host" value="<?= htmlspecialchars($config->redis_host) ?>">
            </div>
            <div class="form-group">
                <label for="redis_port">Port:</label>
                <input type="number" name="redis_port" id="redis_port" value="<?= $config->redis_port ?>">
            </div>
            <div class="form-group">
                <label for="redis_password">Mot de passe (optionnel):</label>
                <input type="password" name="redis_password" id="redis_password" value="<?= htmlspecialchars($config->redis_password ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="redis_database">Base de données:</label>
                <input type="number" name="redis_database" id="redis_database" value="<?= $config->redis_database ?>">
                <small>Numéro de la base Redis (0-15)</small>
            </div>
            <button type="button" class="btn btn-success btn-sm" onclick="testDriver('redis')">Tester Redis</button>
            <span id="test_redis"></span>
        </div>

        <!-- Configuration Memcached -->
        <div id="config_memcached" class="driver-config" style="display: <?= $config->driver === 'memcached' ? 'block' : 'none' ?>">
            <h3>Configuration Memcached</h3>
            <?php
            $memcachedServers = [];
            if (!empty($config->memcached_servers)) {
                $decoded = json_decode($config->memcached_servers, true);
                $memcachedServers = is_array($decoded) ? $decoded : [];
            }
            if (empty($memcachedServers)) {
                $memcachedServers = [['host' => '127.0.0.1', 'port' => 11211]];
            }
            ?>
            <div id="memcached_servers">
                <?php foreach ($memcachedServers as $i => $server): ?>
                    <div class="form-group" style="display: flex; gap: 10px;">
                        <input type="text" name="memcached_hosts[]" placeholder="Hôte" value="<?= htmlspecialchars($server['host']) ?>" style="flex: 2;">
                        <input type="number" name="memcached_ports[]" placeholder="Port" value="<?= $server['port'] ?>" style="flex: 1;">
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-success btn-sm" onclick="testDriver('memcached')">Tester Memcached</button>
            <span id="test_memcached"></span>
        </div>

        <!-- Configuration APCu -->
        <div id="config_apcu" class="driver-config" style="display: <?= $config->driver === 'apcu' ? 'block' : 'none' ?>">
            <h3>Configuration APCu</h3>
            <p>APCu utilise la mémoire partagée PHP. Aucune configuration supplémentaire n'est nécessaire.</p>
            <button type="button" class="btn btn-success btn-sm" onclick="testDriver('apcu')">Tester APCu</button>
            <span id="test_apcu"></span>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Sauvegarder la configuration</button>
        </div>
    </form>
</div>

<script>
    function showDriverConfig(driver) {
        // Cacher toutes les configs
        document.querySelectorAll('.driver-config').forEach(el => el.style.display = 'none');

        // Afficher la config sélectionnée
        const configDiv = document.getElementById('config_' + driver);
        if (configDiv) {
            configDiv.style.display = 'block';
        }
    }

    function testDriver(driver) {
        const testSpan = document.getElementById('test_' + driver);
        testSpan.textContent = ' Test en cours...';
        testSpan.style.color = 'orange';

        // Préparer les données du formulaire
        const formData = new FormData(document.getElementById('cacheConfigForm'));
        formData.append('driver', driver);

        fetch('<?= url('/admin/cache/test-driver') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    testSpan.textContent = ' ✓ ' + data.message;
                    testSpan.style.color = 'green';
                } else {
                    testSpan.textContent = ' ✗ ' + data.message;
                    testSpan.style.color = 'red';
                }
            })
            .catch(error => {
                testSpan.textContent = ' ✗ Erreur réseau';
                testSpan.style.color = 'red';
            });
    }
</script>