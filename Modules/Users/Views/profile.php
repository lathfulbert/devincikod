@extends('backend.layouts.master')

@section('title', 'Mon Profil')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar avatar-xl">
                            <span class="avatar-title rounded-circle bg-primary" style="font-size: 3rem;">
                                <?= strtoupper(substr($user->first_name ?? $user->username ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                    </div>
                    <h4><?= $user->first_name ?? '' ?> <?= $user->last_name ?? '' ?></h4>
                    <p class="text-muted">@<?= $user->username ?? $user->email ?></p>

                    <div class="mt-4">
                        <a href="<?= url('/auth/mfa/settings') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-shield"></i> Paramètres MFA
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informations du profil</h5>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <form method="POST" action="<?= url('/admin/profile/update') ?>">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    value="<?= $user->first_name ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    value="<?= $user->last_name ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= $user->email ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?= $user->username ?? '' ?>">
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Changer le mot de passe</h6>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <small class="text-muted">Laissez vide si vous ne voulez pas changer</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection