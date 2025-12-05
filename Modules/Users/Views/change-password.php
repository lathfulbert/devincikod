@extends('backend.layouts.master')

@section('title', 'Changer le mot de passe')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">🔐 Changer le mot de passe</h5>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <form method="POST" action="<?= url('/admin/profile/change-password') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password"
                                name="current_password" required autofocus>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password"
                                name="new_password" required minlength="8">
                            <small class="text-muted">Minimum 8 caractères</small>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password"
                                name="confirm_password" required minlength="8">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= url('/admin/profile') ?>" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Retour au profil
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Changer le mot de passe
                            </button>
                        </div>
                    </form>

                    <div class="alert alert-info mt-4">
                        <h6 class="alert-heading">
                            <i class="fa fa-info-circle"></i> Conseils de sécurité
                        </h6>
                        <ul class="mb-0 ps-3">
                            <li>Utilisez au moins 8 caractères</li>
                            <li>Mélangez lettres majuscules et minuscules</li>
                            <li>Incluez des chiffres et des caractères spéciaux</li>
                            <li>N'utilisez pas d'informations personnelles</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection