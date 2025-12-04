@extends('backend.layouts.master')

@section('title', 'Éditer Gateway SMS')

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
                    <li class="breadcrumb-item"><a href="<?= url('/admin/settings/sms') ?>">SMS</a></li>
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
                    <h5><i data-feather="server"></i> Éditer <?= htmlspecialchars($gateway->name) ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/settings/sms/gateways/' . $gateway->id . '/update') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?= htmlspecialchars($gateway->name) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Provider</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($gateway->provider_code) ?>" disabled>
                            <small class="text-muted">Le provider ne peut pas être modifié</small>
                        </div>

                        <div class="mb-3">
                            <label for="api_url" class="form-label">API URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="api_url" name="api_url"
                                value="<?= htmlspecialchars($gateway->api_url) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key</label>
                            <input type="text" class="form-control" id="api_key" name="api_key"
                                placeholder="Laissez vide pour ne pas modifier">
                            <small class="text-muted">Actuel: ••••••••</small>
                        </div>

                        <div class="mb-3">
                            <label for="api_secret" class="form-label">API Secret</label>
                            <input type="password" class="form-control" id="api_secret" name="api_secret"
                                placeholder="Laissez vide pour ne pas modifier">
                        </div>

                        <div class="mb-3">
                            <label for="sender_id" class="form-label">Sender ID</label>
                            <input type="text" class="form-control" id="sender_id" name="sender_id"
                                value="<?= htmlspecialchars($gateway->sender_id ?? '') ?>" maxlength="11">
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priorité</label>
                            <input type="number" class="form-control" id="priority" name="priority"
                                value="<?= $gateway->priority ?>" min="0" max="100">
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3"><i data-feather="zap"></i> Limites d'Envoi (Rate Limiting)</h6>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="rate_limit_enabled" name="rate_limit_enabled"
                                    value="1" <?= ($gateway->rate_limit_enabled ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="rate_limit_enabled">
                                    Activer les limites d'envoi
                                </label>
                            </div>
                            <small class="text-muted">Protection contre le dépassement des quotas du provider</small>
                        </div>

                        <div class="row" id="rate_limit_fields">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rate_limit_per_minute" class="form-label">SMS / Minute</label>
                                    <input type="number" class="form-control" id="rate_limit_per_minute" name="rate_limit_per_minute"
                                        value="<?= $gateway->rate_limit_per_minute ?? 60 ?>" min="1">
                                    <small class="text-muted">Max par minute</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rate_limit_per_hour" class="form-label">SMS / Heure</label>
                                    <input type="number" class="form-control" id="rate_limit_per_hour" name="rate_limit_per_hour"
                                        value="<?= $gateway->rate_limit_per_hour ?? 1000 ?>" min="1">
                                    <small class="text-muted">Max par heure</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rate_limit_per_day" class="form-label">SMS / Jour</label>
                                    <input type="number" class="form-control" id="rate_limit_per_day" name="rate_limit_per_day"
                                        value="<?= $gateway->rate_limit_per_day ?? 10000 ?>" min="1">
                                    <small class="text-muted">Max par jour</small>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var checkbox = document.getElementById('rate_limit_enabled');
                                var fields = document.getElementById('rate_limit_fields');

                                function toggleFields() {
                                    fields.style.display = checkbox.checked ? 'flex' : 'none';
                                }

                                checkbox.addEventListener('change', toggleFields);
                                toggleFields(); // Initial state
                            });
                        </script>

                        <hr class="my-4">

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                    value="1" <?= $gateway->is_active ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Activer ce gateway
                                </label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Mettre à jour
                            </button>
                            <a href="<?= url('/admin/settings/sms') ?>" class="btn btn-secondary">
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
</script>
@endsection