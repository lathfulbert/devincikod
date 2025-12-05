@extends('backend.layouts.master')

@section('title', 'Vérification SMS OTP')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📱 Vérification SMS OTP</h5>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <p class="text-muted mb-4">
                        Un code de vérification a été envoyé au <strong><?= htmlspecialchars($phone) ?></strong>.
                        Veuillez entrer ce code ci-dessous pour activer l'authentification à deux facteurs.
                    </p>

                    <form method="POST" action="<?= url('/auth/mfa/setup/sms/verify') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="code" class="form-label">Code de vérification (6 chiffres)</label>
                            <input type="text" class="form-control form-control-lg text-center letter-spacing-2"
                                id="code" name="code" placeholder="000000"
                                maxlength="6" autocomplete="one-time-code" required autofocus>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= url('/auth/mfa/setup/sms') ?>" class="text-decoration-none">
                                <i class="fa fa-arrow-left"></i> Modifier le numéro
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> Vérifier et Activer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .letter-spacing-2 {
        letter-spacing: 0.5em;
        font-size: 1.5rem;
    }
</style>
@endsection