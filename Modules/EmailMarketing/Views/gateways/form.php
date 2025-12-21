
@extends('backend.layouts.master')

@section('title', $edit ? 'Modifier Gateway Email' : 'Ajouter Gateway Email')

@section('content')
<div class="container-fluid">
    <h2><?= $edit ? 'Modifier' : 'Ajouter' ?> un Gateway Email</h2>
    <form action="<?= $edit ? route('admin.email-marketing.gateways.update', ['name' => $gateway['name']]) : route('admin.email-marketing.gateways.store') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="gateway_name" class="form-label">Nom du Gateway</label>
            <input type="text" name="gateway_name" id="gateway_name" class="form-control" value="<?= htmlspecialchars($gateway['name'] ?? '') ?>" <?= $edit ? 'readonly' : '' ?> required>
        </div>
        <div class="mb-3">
            <label for="gateway_type" class="form-label">Type</label>
            <select name="gateway_type" id="gateway_type" class="form-select" required>
                <option value="smtp" <?= ($gateway['type'] ?? '') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                <option value="twilio" <?= ($gateway['type'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio/SendGrid</option>
                <option value="infobip" <?= ($gateway['type'] ?? '') === 'infobip' ? 'selected' : '' ?>>Infobip</option>
                <option value="elasticmail" <?= ($gateway['type'] ?? '') === 'elasticmail' ? 'selected' : '' ?>>ElasticMail</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="from_email" class="form-label">Adresse d'expédition</label>
            <input type="email" name="from_email" id="from_email" class="form-control" value="<?= htmlspecialchars($gateway['from_email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="from_name" class="form-label">Nom d'expéditeur</label>
            <input type="text" name="from_name" id="from_name" class="form-control" value="<?= htmlspecialchars($gateway['from_name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="api_key" class="form-label">API Key (si applicable)</label>
            <input type="text" name="api_key" id="api_key" class="form-control" value="<?= htmlspecialchars($gateway['api_key'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="host" class="form-label">Host SMTP (si applicable)</label>
            <input type="text" name="host" id="host" class="form-control" value="<?= htmlspecialchars($gateway['host'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="port" class="form-label">Port SMTP (si applicable)</label>
            <input type="number" name="port" id="port" class="form-control" value="<?= htmlspecialchars($gateway['port'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username SMTP (si applicable)</label>
            <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($gateway['username'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password SMTP (si applicable)</label>
            <input type="password" name="password" id="password" class="form-control" value="">
        </div>
        <div class="mb-3">
            <label for="encryption" class="form-label">Encryption SMTP</label>
            <select name="encryption" id="encryption" class="form-select">
                <option value="tls" <?= ($gateway['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                <option value="ssl" <?= ($gateway['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                <option value="" <?= empty($gateway['encryption']) ? 'selected' : '' ?>>Aucune</option>
            </select>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="active" id="active" class="form-check-input" <?= !empty($gateway['active']) ? 'checked' : '' ?>>
            <label for="active" class="form-check-label">Activer ce gateway</label>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="<?= route('admin.email-marketing.gateways.index') ?>" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
