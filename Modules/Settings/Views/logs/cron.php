<?php include __DIR__ . '/../../../Core/Views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5>📋 Logs Cron</h5>
                    <div>
                        <a href="/admin/logs/health" class="btn btn-sm btn-info">Health Logs</a>
                        <a href="/admin/logs/sms-queue" class="btn btn-sm btn-warning">SMS Queue Logs</a>
                        <button onclick="clearLog('cron')" class="btn btn-sm btn-danger">🗑️ Vider les logs</button>
                        <button onclick="location.reload()" class="btn btn-sm btn-secondary">🔄 Actualiser</button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!$exists): ?>
                        <div class="alert alert-warning">
                            ⚠️ Le fichier de log n'existe pas encore: <code><?= htmlspecialchars($logPath) ?></code>
                            <br><br>
                            Le cron n'a probablement pas encore été exécuté.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            📁 Fichier: <code><?= htmlspecialchars($logPath) ?></code>
                            <br>
                            📊 Affichage des 200 dernières lignes
                        </div>

                        <div class="bg-dark text-light p-3 rounded" style="max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                            <?php if (empty($logs)): ?>
                                <em>Aucun log disponible</em>
                            <?php else: ?>
                                <?php foreach ($logs as $line): ?>
                                    <?php
                                    // Colorer les lignes selon le contenu
                                    $class = '';
                                    if (stripos($line, 'error') !== false || stripos($line, 'fail') !== false) {
                                        $class = 'text-danger';
                                    } elseif (stripos($line, 'success') !== false || stripos($line, '✅') !== false) {
                                        $class = 'text-success';
                                    } elseif (stripos($line, 'warning') !== false || stripos($line, '⚠') !== false) {
                                        $class = 'text-warning';
                                    }
                                    ?>
                                    <div class="<?= $class ?>"><?= htmlspecialchars($line) ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearLog(type) {
    if (!confirm('Êtes-vous sûr de vouloir vider ce log ?')) {
        return;
    }

    fetch('/admin/logs/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'log_type=' + type
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur: ' + error.message);
    });
}

// Auto-refresh toutes les 30 secondes
setTimeout(() => location.reload(), 30000);
</script>

<?php include __DIR__ . '/../../../Core/Views/partials/footer.php'; ?>
