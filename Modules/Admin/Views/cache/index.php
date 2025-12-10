@extends('backend.layouts.master')

@section('title', $title ?? 'Cache')

@section('styles')
<!-- Styles additionnels si nécessaires pour cette page -->
@endsection

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Cache' ?></h3>
            </div>
            <div class="col-6">
                <?php
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Configuration'],
                    ['label' => 'Cache']
                ];
                component('breadcrumb');
                ?>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <!-- Section: Actions rapides -->
    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="<?= url('/admin/cache/stats') ?>" class="btn btn-info">
                <i data-feather="bar-chart-2"></i> Statistiques
            </a>
            <form method="POST" action="<?= url('/admin/cache/clear') ?>" style="display: inline;" onsubmit="return confirm('Vider tout le cache ?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning">
                    <i data-feather="trash-2"></i> Vider tout
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Card: Configuration Générale -->
        <div class="col-lg-12">
            <?php
            component('card-start', ['card_title' => "Configuration du Cache", 'card_actions' => '']);
            ?>

            <form method="POST" action="<?= url('/admin/cache/update') ?>" id="cacheConfigForm">
                <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">

                <div class="row">
                    <!-- Activer le cache -->
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="cacheEnabled" <?= $config->enabled ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cacheEnabled">
                                <strong>Activer le système de cache</strong>
                            </label>
                            <small class="form-text text-muted d-block">Active ou désactive complètement le système de cache</small>
                        </div>
                    </div>

                    <!-- Driver de cache -->
                    <div class="col-md-6 mb-3">
                        <label for="driver" class="form-label">Driver de cache <span class="text-danger">*</span></label>
                        <select name="driver" id="driver" class="form-select" onchange="showDriverConfig(this.value)" required>
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
                        <small class="form-text text-muted">Système de stockage utilisé pour le cache</small>
                    </div>

                    <!-- TTL par défaut -->
                    <div class="col-md-6 mb-3">
                        <label for="default_ttl" class="form-label">TTL par défaut (secondes)</label>
                        <input type="number" name="default_ttl" id="default_ttl" class="form-control" value="<?= $config->default_ttl ?>" min="60">
                        <small class="form-text text-muted">Durée de vie par défaut des éléments en cache</small>
                    </div>

                    <!-- Préfixe -->
                    <div class="col-md-6 mb-3">
                        <label for="prefix" class="form-label">Préfixe des clés</label>
                        <input type="text" name="prefix" id="prefix" class="form-control" value="<?= $config->prefix ?>">
                        <small class="form-text text-muted">Préfixe ajouté à toutes les clés de cache</small>
                    </div>

                    <!-- Serializer -->
                    <div class="col-md-6 mb-3">
                        <label for="serializer" class="form-label">Sérialisateur</label>
                        <select name="serializer" id="serializer" class="form-select">
                            <option value="php" <?= $config->serializer === 'php' ? 'selected' : '' ?>>PHP serialize (par défaut)</option>
                            <option value="json" <?= $config->serializer === 'json' ? 'selected' : '' ?>>JSON</option>
                            <option value="igbinary" <?= $config->serializer === 'igbinary' ? 'selected' : '' ?> <?= !extension_loaded('igbinary') ? 'disabled' : '' ?>>
                                Igbinary <?= !extension_loaded('igbinary') ? '(non disponible)' : '' ?>
                            </option>
                        </select>
                        <small class="form-text text-muted">Méthode de sérialisation des données</small>
                    </div>
                </div>

                <!-- Configuration spécifique au driver -->
                <div id="driver-configs">
                    <!-- Filesystem -->
                    <div id="filesystem-config" class="driver-config border-top pt-4 mt-4" style="display: none;">
                        <h5 class="mb-3"><i data-feather="folder" class="me-2"></i>Configuration Filesystem</h5>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="filesystem_path" class="form-label">Chemin de stockage</label>
                                <input type="text" name="filesystem_path" id="filesystem_path" class="form-control" value="<?= $config->drivers->filesystem->path ?? '' ?>">
                                <small class="form-text text-muted">Répertoire où seront stockés les fichiers de cache</small>
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="testDriver('filesystem')">
                                    <i data-feather="zap" style="width: 14px; height: 14px;"></i> Tester Filesystem
                                </button>
                                <span id="test_filesystem" class="ms-2"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Redis -->
                    <div id="redis-config" class="driver-config border-top pt-4 mt-4" style="display: none;">
                        <h5 class="mb-3"><i data-feather="database" class="me-2"></i>Configuration Redis</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="redis_host" class="form-label">Hôte</label>
                                <input type="text" name="redis_host" id="redis_host" class="form-control" value="<?= $config->drivers->redis->host ?? '' ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="redis_port" class="form-label">Port</label>
                                <input type="number" name="redis_port" id="redis_port" class="form-control" value="<?= $config->drivers->redis->port ?? '' ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="redis_database" class="form-label">Base de données</label>
                                <input type="number" name="redis_database" id="redis_database" class="form-control" value="<?= $config->drivers->redis->database ?? '' ?>" min="0" max="15">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="redis_password" class="form-label">Mot de passe (optionnel)</label>
                                <input type="password" name="redis_password" id="redis_password" class="form-control" value="<?= $config->drivers->redis->password ?? '' ?>" autocomplete="off">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="redis_timeout" class="form-label">Timeout (secondes)</label>
                                <input type="number" name="redis_timeout" id="redis_timeout" class="form-control" value="<?= $config->drivers->redis->timeout ?? '' ?>" step="0.1">
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="testDriver('redis')">
                                    <i data-feather="zap" style="width: 14px; height: 14px;"></i> Tester Redis
                                </button>
                                <span id="test_redis" class="ms-2"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Memcached -->
                    <div id="memcached-config" class="driver-config border-top pt-4 mt-4" style="display: none;">
                        <h5 class="mb-3"><i data-feather="server" class="me-2"></i>Configuration Memcached</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="memcached_host" class="form-label">Hôte</label>
                                <input type="text" name="memcached_host" id="memcached_host" class="form-control" value="<?= $config->drivers->memcached->host ?? '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="memcached_port" class="form-label">Port</label>
                                <input type="number" name="memcached_port" id="memcached_port" class="form-control" value="<?= $config->drivers->memcached->port ?? '' ?>">
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="testDriver('memcached')">
                                    <i data-feather="zap" style="width: 14px; height: 14px;"></i> Tester Memcached
                                </button>
                                <span id="test_memcached" class="ms-2"></span>
                            </div>
                        </div>
                    </div>

                    <!-- APCu -->
                    <div id="apcu-config" class="driver-config border-top pt-4 mt-4" style="display: none;">
                        <h5 class="mb-3"><i data-feather="cpu" class="me-2"></i>Configuration APCu</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i data-feather="info"></i>
                                    APCu est un cache en mémoire partagée. Aucune configuration supplémentaire n'est requise.
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="testDriver('apcu')">
                                    <i data-feather="zap" style="width: 14px; height: 14px;"></i> Tester APCu
                                </button>
                                <span id="test_apcu" class="ms-2"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save"></i> Enregistrer la configuration
                        </button>
                        <a href="<?= url('/admin/cache') ?>" class="btn btn-secondary">
                            <i data-feather="x"></i> Annuler
                        </a>
                    </div>
                </div>
            </form>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Afficher la config du driver sélectionné
    function showDriverConfig(driver) {
        // Cacher toutes les configs
        document.querySelectorAll('.driver-config').forEach(function(el) {
            el.style.display = 'none';
        });

        // Afficher la config du driver sélectionné
        const configDiv = document.getElementById(driver + '-config');
        if (configDiv) {
            configDiv.style.display = 'block';
        }
    }

    // Tester la connexion à un driver
    function testDriver(driver) {
        const testSpan = document.getElementById('test_' + driver);
        testSpan.innerHTML = '<span class="badge bg-warning">Test en cours...</span>';

        const formData = new FormData(document.getElementById('cacheConfigForm'));
        formData.set('driver', driver);

        fetch('<?= url('/admin/cache/test-driver') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    testSpan.innerHTML = '<span class="badge bg-success"><i data-feather="check"></i> ' + data.message + '</span>';
                } else {
                    testSpan.innerHTML = '<span class="badge bg-danger"><i data-feather="x"></i> ' + data.message + '</span>';
                }
                // Réinitialiser les icônes Feather
                feather.replace();
            })
            .catch(error => {
                testSpan.innerHTML = '<span class="badge bg-danger"><i data-feather="alert-triangle"></i> ' + error.message + '</span>';
                feather.replace();
            });
    }

    // Afficher la config du driver actuel au chargement
    document.addEventListener('DOMContentLoaded', function() {
        const currentDriver = document.getElementById('driver').value;
        showDriverConfig(currentDriver);
    });
</script>
@endsection