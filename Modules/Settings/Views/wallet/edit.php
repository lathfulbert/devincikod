@extends('backend.layouts.master')

@section('title', 'Éditer Gateway Wallet')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/settings') ?>">Settings</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/settings/wallet') ?>">Wallet</a></li>
                    <li class="breadcrumb-item active">Éditer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="credit-card"></i> Éditer <?= htmlspecialchars($gateway->name) ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/settings/wallet/gateways/' . $gateway->id . '/update') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?= htmlspecialchars($gateway->name) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Provider</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($gateway->provider_code) ?>" disabled>
                            <small class="text-muted">Le provider ne peut pas être modifié après création</small>
                        </div>

                        <div class="mb-3">
                            <label for="api_url" class="form-label">API URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="api_url" name="api_url"
                                value="<?= htmlspecialchars($gateway->api_url) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="api_key" class="form-label">API Key</label>
                                <input type="text" class="form-control" id="api_key" name="api_key"
                                    placeholder="Laissez vide pour ne pas modifier">
                                <small class="text-muted">Actuel: ••••••••</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="api_secret" class="form-label">API Secret</label>
                                <input type="password" class="form-control" id="api_secret" name="api_secret"
                                    placeholder="Laissez vide pour ne pas modifier">
                                <small class="text-muted">Actuel: ••••••••</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="merchant_id" class="form-label">Merchant ID</label>
                            <input type="text" class="form-control" id="merchant_id" name="merchant_id"
                                placeholder="Laissez vide pour ne pas modifier">
                            <small class="text-muted">Actuel: <?= $gateway->merchant_id ? '••••••••' : 'Non défini' ?></small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Devise <span class="text-danger">*</span></label>
                                <select class="form-control" id="currency" name="currency" required>
                                    <option value="XOF" <?= $gateway->currency === 'XOF' ? 'selected' : '' ?>>XOF - Franc CFA</option>
                                    <option value="EUR" <?= $gateway->currency === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                    <option value="USD" <?= $gateway->currency === 'USD' ? 'selected' : '' ?>>USD - Dollar</option>
                                    <option value="GNF" <?= $gateway->currency === 'GNF' ? 'selected' : '' ?>>GNF - Franc Guinéen</option>
                                    <option value="MAD" <?= $gateway->currency === 'MAD' ? 'selected' : '' ?>>MAD - Dirham Marocain</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="transaction_fee" class="form-label">Frais de Transaction (%)</label>
                                <input type="number" class="form-control" id="transaction_fee" name="transaction_fee"
                                    value="<?= $gateway->transaction_fee ?>" min="0" max="100" step="0.01">
                                <small class="text-muted">Pourcentage de frais appliqués</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Configuration Additionnelle (JSON)</label>
                            <textarea class="form-control" name="configuration" rows="4"><?= htmlspecialchars(json_encode($gateway->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></textarea>
                            <small class="text-muted">Configuration spécifique au provider au format JSON</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                    value="1" <?= $gateway->is_active ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Activer ce gateway
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default"
                                    value="1" <?= $gateway->is_default ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_default">
                                    Définir comme gateway par défaut
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i data-feather="alert-triangle"></i>
                            <strong>Attention:</strong> Les informations sensibles sont chiffrées. Si vous modifiez une clé API, assurez-vous d'entrer la nouvelle valeur complète.
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Mettre à jour
                            </button>
                            <a href="<?= url('/admin/settings/wallet') ?>" class="btn btn-secondary">
                                Annuler
                            </a>

                            <?php if (!$gateway->is_default): ?>
                                <a href="<?= url('/admin/settings/wallet/gateways/' . $gateway->id . '/set-default') ?>"
                                   class="btn btn-info"
                                   onclick="return confirm('Définir ce gateway comme défaut ?')">
                                    <i data-feather="star"></i> Définir par défaut
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test Connection Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i data-feather="zap"></i> Test de Connexion</h5>
                </div>
                <div class="card-body">
                    <p>Testez la connexion avec ce gateway pour vérifier que les credentials sont corrects.</p>
                    <button type="button" class="btn btn-outline-primary" id="testConnection">
                        <i data-feather="activity"></i> Tester la connexion
                    </button>
                    <div id="testResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Test connection functionality
    document.getElementById('testConnection').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('testResult');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Test en cours...';

        fetch('<?= url('/admin/settings/wallet/gateways/' . $gateway->id . '/test') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="alert alert-success"><i data-feather="check-circle"></i> ' + data.message + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger"><i data-feather="x-circle"></i> ' + data.message + '</div>';
                }
                feather.replace();
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i data-feather="x-circle"></i> Erreur lors du test</div>';
                feather.replace();
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i data-feather="activity"></i> Tester la connexion';
                feather.replace();
            });
    });
</script>
@endsection
