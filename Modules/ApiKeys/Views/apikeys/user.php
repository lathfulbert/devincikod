@extends('backend.layouts.master')

@section('title', 'Ma Clé API')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">🔑 Ma Clé API Personnelle</h5>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <?php if (isset($_SESSION['new_api_key'])): ?>
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fa fa-exclamation-triangle"></i> Clé API générée - Copiez-la maintenant !
                            </h6>
                            <p class="mb-2">Cette clé ne sera plus affichée. Conservez-la en lieu sûr.</p>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" id="newApiKey"
                                    value="<?= $_SESSION['new_api_key'] ?>" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyKey()">
                                    <i class="fa fa-copy"></i> Copier
                                </button>
                            </div>
                        </div>
                        <?php unset($_SESSION['new_api_key']); ?>
                    <?php endif; ?>

                    <?php if ($apiKey): ?>
                        <div class="mb-4">
                            <h6>Clé API Active</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Nom :</strong> <?= $apiKey->name ?? 'Personal API Key' ?></p>

                                            <!-- DEBUG -->
                                            <p class="mb-1 text-danger"><strong>DEBUG created_at RAW:</strong> <?= var_export($apiKey->created_at, true) ?></p>

                                            <p class="mb-1"><strong>Créée le :</strong>
                                                <?= $apiKey->created_at ? date('d/m/Y H:i', strtotime($apiKey->created_at)) : 'N/A' ?>
                                            </p>
                                            <p class="mb-0"><strong>Dernière utilisation :</strong>
                                                <?= $apiKey->last_used_at ? date('d/m/Y H:i', strtotime($apiKey->last_used_at)) : 'Jamais' ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <span class="badge bg-success mb-2">Active</span><br>
                                            <p class="text-muted small mb-0">Clé: sk_••••••••••••••••••</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <form method="POST" action="<?= url('/admin/api-keys/generate') ?>"
                                onsubmit="return confirm('Régénérer une nouvelle clé révoquera l\'ancienne. Continuer ?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-refresh"></i> Régénérer la clé
                                </button>
                            </form>

                            <form method="POST" action="<?= url('/admin/api-keys/revoke') ?>"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir révoquer votre clé API ?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-ban"></i> Révoquer la clé
                                </button>
                            </form>
                        </div>
                    <?php elseif (!isset($_SESSION['new_api_key'])): ?>
                        <div class="text-center py-5">
                            <i class="fa fa-key fa-3x text-muted mb-3"></i>
                            <h5>Vous n'avez pas encore de clé API</h5>
                            <p class="text-muted">Générez une clé API pour accéder aux services programmatiquement</p>

                            <form method="POST" action="<?= url('/admin/api-keys/generate') ?>" class="mt-3">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Générer une clé API
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fa fa-info-circle"></i> Comment utiliser votre clé API
                        </h6>
                        <p class="mb-2">Incluez votre clé dans l'en-tête de vos requêtes HTTP :</p>
                        <pre class="bg-dark text-white p-3 rounded"><code>Authorization: Bearer YOUR_API_KEY</code></pre>
                        <p class="mb-0 small">
                            <strong>Exemple cURL :</strong><br>
                            <code>curl -H "Authorization: Bearer YOUR_API_KEY" <?= url('/api/endpoint') ?></code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyKey() {
        const keyInput = document.getElementById('newApiKey');
        keyInput.select();
        document.execCommand('copy');

        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check"></i> Copié !';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');

        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }
</script>
@endsection