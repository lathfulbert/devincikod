@extends('backend.layouts.master')

@section('title', 'Ajouter Gateway Wallet')

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
                    <li class="breadcrumb-item active">Ajouter</li>
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
                    <h5><i data-feather="credit-card"></i> Nouveau Gateway Wallet</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/settings/wallet/gateways/store') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Ex: PayDunya Production">
                        </div>

                        <div class="mb-3">
                            <label for="provider_code" class="form-label">Provider <span class="text-danger">*</span></label>
                            <select class="form-control" id="provider_code" name="provider_code" required>
                                <option value="">-- Sélectionner un provider --</option>
                                <option value="paydunya">PayDunya</option>
                                <option value="cinetpay">CinetPay</option>
                                <option value="orange_money">Orange Money</option>
                                <option value="wave">Wave</option>
                                <option value="moov_money">Moov Money</option>
                                <option value="mtn_mobile_money">MTN Mobile Money</option>
                                <option value="custom">Autre (Custom)</option>
                            </select>
                            <small class="text-muted">Sélectionnez le fournisseur de paiement</small>
                        </div>

                        <div class="mb-3">
                            <label for="api_url" class="form-label">API URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="api_url" name="api_url" required placeholder="https://api.provider.com/v1">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="api_key" name="api_key" required>
                                <small class="text-muted">Clé publique ou API Key</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="api_secret" class="form-label">API Secret</label>
                                <input type="password" class="form-control" id="api_secret" name="api_secret">
                                <small class="text-muted">Clé privée (optionnel)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="merchant_id" class="form-label">Merchant ID</label>
                            <input type="text" class="form-control" id="merchant_id" name="merchant_id">
                            <small class="text-muted">Identifiant marchand (si requis par le provider)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Devise <span class="text-danger">*</span></label>
                                <select class="form-control" id="currency" name="currency" required>
                                    <option value="XOF" selected>XOF - Franc CFA</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="USD">USD - Dollar</option>
                                    <option value="GNF">GNF - Franc Guinéen</option>
                                    <option value="MAD">MAD - Dirham Marocain</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="transaction_fee" class="form-label">Frais de Transaction (%)</label>
                                <input type="number" class="form-control" id="transaction_fee" name="transaction_fee" value="0" min="0" max="100" step="0.01">
                                <small class="text-muted">Pourcentage de frais (ex: 2.5 pour 2.5%)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Configuration Additionnelle (JSON)</label>
                            <textarea class="form-control" name="configuration" rows="4" placeholder='{"webhook_url": "https://...", "timeout": 30}'></textarea>
                            <small class="text-muted">Configuration spécifique au provider au format JSON (optionnel)</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Activer ce gateway
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                                <label class="form-check-label" for="is_default">
                                    Définir comme gateway par défaut
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i data-feather="info"></i>
                            <strong>Note:</strong> Les informations sensibles (API Key, Secret, Merchant ID) seront automatiquement chiffrées avant d'être stockées en base de données.
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Enregistrer
                            </button>
                            <a href="<?= url('/admin/settings/wallet') ?>" class="btn btn-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Auto-fill API URL based on provider selection
    document.getElementById('provider_code').addEventListener('change', function() {
        const apiUrlField = document.getElementById('api_url');
        const provider = this.value;

        const providerUrls = {
            'paydunya': 'https://app.paydunya.com/api/v1',
            'cinetpay': 'https://api-checkout.cinetpay.com/v2',
            'orange_money': 'https://api.orange.com/orange-money-webpay/dev/v1',
            'wave': 'https://api.wave.com/v1',
            'moov_money': 'https://api.moov-africa.com/v1',
            'mtn_mobile_money': 'https://api.mtn.com/v1'
        };

        if (providerUrls[provider]) {
            apiUrlField.value = providerUrls[provider];
        } else if (provider !== 'custom') {
            apiUrlField.value = '';
        }
    });
</script>
@endsection
