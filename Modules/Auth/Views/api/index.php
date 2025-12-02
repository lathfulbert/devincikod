@extends('backend.layouts.master')

@section('title', 'Gestion des Clés API')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'API Keys']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php
            $card_title = "Mes Clés API";
            component('card-start');
            ?>

            <div class="alert alert-info">
                <i data-feather="info"></i>
                <strong>Information :</strong> Les clés API vous permettent d'accéder aux services de manière programmatique.
                Gardez vos clés secrètes et ne les partagez jamais.
            </div>

            <?php if ($hasApiKey): ?>
                <div class="mb-4">
                    <h5>Votre Clé API Actuelle</h5>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace"
                               id="api-key"
                               value="<?= htmlspecialchars($user->api_key ?? '') ?>"
                               readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                            <i data-feather="copy"></i> Copier
                        </button>
                    </div>
                    <small class="text-muted">
                        Créée le : <?= date('d/m/Y H:i', strtotime($user->api_key_created_at ?? 'now')) ?>
                    </small>
                </div>

                <div class="alert alert-warning">
                    <i data-feather="alert-triangle"></i>
                    <strong>Attention :</strong> Révoquer votre clé API actuelle la rendra inutilisable.
                    Toutes les applications utilisant cette clé cesseront de fonctionner.
                </div>

                <form action="<?= url('/admin/api-keys/revoke') ?>" method="POST"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir révoquer cette clé API ?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">
                        <i data-feather="x-circle"></i> Révoquer la Clé API
                    </button>
                </form>

            <?php else: ?>
                <div class="text-center py-5">
                    <i data-feather="key" style="width: 64px; height: 64px; color: #6c757d;"></i>
                    <h4 class="mt-3">Aucune Clé API</h4>
                    <p class="text-muted">Vous n'avez pas encore de clé API. Générez-en une pour commencer.</p>

                    <form action="<?= url('/admin/api-keys/generate') ?>" method="POST" class="mt-4">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="plus-circle"></i> Générer une Clé API
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <hr class="my-5">

            <div class="row">
                <div class="col-md-6">
                    <h5>Documentation</h5>
                    <p>Consultez la documentation pour apprendre à utiliser l'API :</p>
                    <a href="<?= url('/admin/api-keys/docs') ?>" class="btn btn-outline-primary">
                        <i data-feather="book"></i> Voir la Documentation
                    </a>
                </div>

                <div class="col-md-6">
                    <h5>Sécurité</h5>
                    <ul class="list-unstyled">
                        <li><i data-feather="check-circle" class="text-success"></i> Ne partagez jamais votre clé</li>
                        <li><i data-feather="check-circle" class="text-success"></i> Utilisez HTTPS uniquement</li>
                        <li><i data-feather="check-circle" class="text-success"></i> Révoquez les clés compromises</li>
                        <li><i data-feather="check-circle" class="text-success"></i> Surveillez l'utilisation</li>
                    </ul>
                </div>
            </div>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function copyApiKey() {
    const input = document.getElementById('api-key');
    input.select();
    document.execCommand('copy');

    // Visual feedback
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i data-feather="check"></i> Copié !';
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        feather.replace();
    }, 2000);
}

// Refresh feather icons
feather.replace();
</script>
@endsection
