@extends('backend.layouts.master')

@section('title', 'Configuration SMS OTP')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Configuration SMS OTP</h3>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>


                    <form action="<?= url('/admin/auth/sms-config') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="sender_id" class="form-label">Sender ID</label>
                            <select name="sender_id" id="sender_id" class="form-select">
                                <option value="AUTH" <?= selected('AUTH', $currentSenderId ?? '') ?>>AUTH (Défaut)</option>
                                <?php foreach ($senderNames as $sender): ?>
                                    <option value="<?= htmlspecialchars($sender->name) ?>" <?= selected($sender->name, $currentSenderId ?? '') ?>>
                                        <?= htmlspecialchars($sender->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">L'identifiant d'expéditeur qui s'affichera sur le téléphone de l'utilisateur.</div>
                        </div>

                        <div class="mb-3">
                            <label for="gateway_code" class="form-label">Gateway SMS</label>
                            <select name="gateway_code" id="gateway_code" class="form-select">
                                <option value="auto" <?= selected('auto', $currentGateway ?? '') ?>>Automatique (Gateway par défaut)</option>
                                <?php foreach ($gateways as $gateway): ?>
                                    <option value="<?= htmlspecialchars($gateway->provider_code) ?>" <?= selected($gateway->provider_code, $currentGateway ?? '') ?>>
                                        <?= htmlspecialchars($gateway->name) ?> (<?= htmlspecialchars($gateway->provider_code) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Le fournisseur utilisé pour envoyer les SMS OTP.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection