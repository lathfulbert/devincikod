@extends('backend.layouts.master')

@section('title', 'Configurer TOTP')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Configurer Google Authenticator</h3>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <div class="text-center mb-4">
                        <h5>Étape 1 : Scannez le QR Code</h5>
                        <p class="text-muted">Utilisez Google Authenticator ou Authy pour scanner ce code</p>

                        <img src="<?= $qr_code_url ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 300px;">

                        <div class="alert alert-info">
                            <strong>Secret manuel :</strong>
                            <code><?= $secret ?></code>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5>Étape 2 : Codes de récupération</h5>
                        <p class="text-muted">Conservez ces codes en lieu sûr. Ils vous permettront de vous connecter si vous perdez votre téléphone.</p>

                        <div class="alert alert-warning">
                            <?php foreach ($backup_codes as $code): ?>
                                <code class="d-block"><?= $code ?></code>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr>

                    <div>
                        <h5>Étape 3 : Vérification</h5>
                        <p class="text-muted">Entrez le code affiché dans votre application pour confirmer la configuration</p>

                        <form method="POST" action="<?= url('/auth/mfa/setup/totp/verify') ?>">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="code" class="form-label">Code de vérification (6 chiffres)</label>
                                <input type="text" class="form-control" id="code" name="code"
                                    maxlength="6" pattern="[0-9]{6}" required autofocus>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="<?= url('/auth/mfa/settings') ?>" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">Vérifier et Activer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection