@extends('backend.layouts.master')

@section('title', 'Ajouter Gateway SMS')

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
                    <h5><i data-feather="server"></i> Nouveau Gateway SMS</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/settings/sms/gateways/store') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="provider_code" class="form-label">Provider <span class="text-danger">*</span></label>
                            <select class="form-control" id="provider_code" name="provider_code" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="orange_ci">Orange Côte d'Ivoire</option>
                                <option value="infobip">Infobip</option>
                                <option value="custom">Autre (Custom)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="api_url" class="form-label">API URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="api_url" name="api_url" required>
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="api_key" name="api_key" required>
                        </div>

                        <div class="mb-3">
                            <label for="api_secret" class="form-label">API Secret</label>
                            <input type="password" class="form-control" id="api_secret" name="api_secret">
                            <small class="text-muted">Laissez vide si non requis</small>
                        </div>

                        <div class="mb-3">
                            <label for="sender_id" class="form-label">Sender ID</label>
                            <input type="text" class="form-control" id="sender_id" name="sender_id" maxlength="11">
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priorité</label>
                            <input type="number" class="form-control" id="priority" name="priority" value="0" min="0" max="100">
                            <small class="text-muted">Plus élevé = plus de priorité (failover)</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Activer ce gateway
                                </label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Enregistrer
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