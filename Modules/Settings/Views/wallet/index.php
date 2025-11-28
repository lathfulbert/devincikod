@extends('backend.layouts.master')

@section('title', 'Configuration Wallet')

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
                    <li class="breadcrumb-item active">Wallet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <form method="POST" action="<?= url('/admin/settings/wallet/update') ?>">
        <?= csrf_field() ?>

        <div class="row">
            <!-- General Settings -->
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i data-feather="settings"></i> Paramètres Généraux</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Devise</label>
                                    <select class="form-control" id="currency" name="currency">
                                        <option value="XOF" <?= ($settings['currency'] ?? 'XOF') === 'XOF' ? 'selected' : '' ?>>XOF (Franc CFA)</option>
                                        <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                                        <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD (Dollar)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_topup" class="form-label">Montant Minimum Top-up</label>
                                    <input type="number" class="form-control" id="min_topup" name="min_topup"
                                        value="<?= $settings['min_topup'] ?? 1000 ?>" min="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Gateways -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i data-feather="credit-card"></i> Gateways de Paiement</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Gateway</th>
                                        <th>Provider</th>
                                        <th>Devise</th>
                                        <th>Frais</th>
                                        <th>Statut</th>
                                        <th>Par défaut</th>
                                        <th>Configuration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gateways as $gateway): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($gateway->name) ?></strong></td>
                                            <td><code><?= htmlspecialchars($gateway->provider_code) ?></code></td>
                                            <td><?= $gateway->currency ?></td>
                                            <td><?= $gateway->transaction_fee ?>%</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="gateways[<?= $gateway->id ?>][is_active]"
                                                        value="1" <?= $gateway->is_active ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($gateway->is_default): ?>
                                                    <span class="badge badge-primary">Défaut</span>
                                                <?php else: ?>
                                                    <a href="<?= url('/admin/settings/wallet/gateways/' . $gateway->id . '/set-default') ?>"
                                                        class="btn btn-sm btn-outline-primary">Définir</a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#config-<?= $gateway->id ?>">
                                                    <i data-feather="settings"></i> Configurer
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="config-<?= $gateway->id ?>">
                                            <td colspan="7" class="bg-light">
                                                <div class="p-3">
                                                    <h6>Configuration <?= htmlspecialchars($gateway->name) ?></h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label class="form-label">API Key</label>
                                                            <input type="password" class="form-control"
                                                                name="gateways[<?= $gateway->id ?>][api_key]"
                                                                placeholder="••••••••">
                                                            <small class="text-muted">Laissez vide pour ne pas modifier</small>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">API Secret</label>
                                                            <input type="password" class="form-control"
                                                                name="gateways[<?= $gateway->id ?>][api_secret]"
                                                                placeholder="••••••••">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Merchant ID</label>
                                                            <input type="password" class="form-control"
                                                                name="gateways[<?= $gateway->id ?>][merchant_id]"
                                                                placeholder="••••••••">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="col-md-12 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save"></i> Enregistrer la Configuration
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection