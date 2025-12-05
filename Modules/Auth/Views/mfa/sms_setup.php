@extends('backend.layouts.master')

@section('title', 'Activer SMS OTP')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📱 Activer SMS OTP</h5>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <p class="text-muted mb-4">
                        Vous recevrez un code de vérification par SMS à chaque connexion.
                    </p>

                    <form method="POST" action="<?= url('/auth/mfa/setup') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="method" value="sms">

                        <div class="mb-3">
                            <label for="phone" class="form-label">Numéro de téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                placeholder="+33612345678" required autofocus>
                            <small class="text-muted">Format international (ex: +33612345678, +225XXXXXXXXXX)</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Note :</strong> Assurez-vous que le module SmsCore est configuré pour recevoir les codes par SMS.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= url('/auth/mfa/settings') ?>" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> Activer SMS OTP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection