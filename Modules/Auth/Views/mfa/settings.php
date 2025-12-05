@extends('backend.layouts.master')

@section('title', 'Paramètres MFA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Authentification Multi-Facteurs (MFA)</h3>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <p class="text-muted mb-4">
                        Renforcez la sécurité de votre compte en activant l'authentification à deux facteurs.
                    </p>

                    <div class="row">
                        <!-- TOTP (Google Authenticator) -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-phone"></i> Google Authenticator (TOTP)
                                    </h5>
                                    <p class="card-text">
                                        Utilisez une application comme Google Authenticator ou Authy pour générer des codes de vérification.
                                    </p>

                                    <?php
                                    $totpStatus = 'none';
                                    foreach ($methods as $method) {
                                        if ($method['type'] === 'totp') {
                                            $totpStatus = $method['is_verified'] ? 'active' : 'pending';
                                            break;
                                        }
                                    }
                                    ?>

                                    <?php if ($totpStatus === 'active'): ?>
                                        <span class="badge bg-success mb-2">Activé</span>
                                        <form method="POST" action="<?= url('/auth/mfa/disable') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="totp">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Désactiver TOTP ?')">
                                                Désactiver
                                            </button>
                                        </form>
                                    <?php elseif ($totpStatus === 'pending'): ?>
                                        <span class="badge bg-warning text-dark mb-2">En attente de vérification</span>
                                        <form method="POST" action="<?= url('/auth/mfa/setup') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="totp">
                                            <button type="submit" class="btn btn-warning w-100 mb-2">
                                                Reprendre la configuration
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('/auth/mfa/setup') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="totp">
                                            <button type="submit" class="btn btn-primary">
                                                Activer TOTP
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- SMS OTP -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-chat-dots"></i> SMS OTP
                                    </h5>
                                    <p class="card-text">
                                        Recevez un code de vérification par SMS sur votre téléphone.
                                    </p>

                                    <?php
                                    $smsStatus = 'none';
                                    foreach ($methods as $method) {
                                        if ($method['type'] === 'sms') {
                                            $smsStatus = $method['is_verified'] ? 'active' : 'pending';
                                            break;
                                        }
                                    }
                                    ?>

                                    <?php if ($smsStatus === 'active'): ?>
                                        <span class="badge bg-success mb-2">Activé</span>
                                        <form method="POST" action="<?= url('/auth/mfa/disable') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="sms">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Désactiver SMS OTP ?')">
                                                Désactiver
                                            </button>
                                        </form>
                                    <?php elseif ($smsStatus === 'pending'): ?>
                                        <span class="badge bg-warning text-dark mb-2">En attente de vérification</span>
                                        <a href="<?= url('/auth/mfa/setup/sms/verify') ?>" class="btn btn-warning w-100 mb-2">
                                            Vérifier le code
                                        </a>
                                        <form method="POST" action="<?= url('/auth/mfa/disable') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="sms">
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Annuler la configuration ?')">
                                                Annuler
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="<?= url('/auth/mfa/setup/sms') ?>" class="btn btn-primary">
                                            Activer SMS
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Email OTP -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-envelope"></i> Email OTP
                                    </h5>
                                    <p class="card-text">
                                        Recevez un code de vérification par email.
                                    </p>

                                    <?php
                                    $emailStatus = 'none';
                                    foreach ($methods as $method) {
                                        if ($method['type'] === 'email') {
                                            $emailStatus = $method['is_verified'] ? 'active' : 'pending';
                                            break;
                                        }
                                    }
                                    ?>

                                    <?php if ($emailStatus === 'active'): ?>
                                        <span class="badge bg-success mb-2">Activé</span>
                                        <form method="POST" action="<?= url('/auth/mfa/disable') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="email">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Désactiver Email OTP ?')">
                                                Désactiver
                                            </button>
                                        </form>
                                    <?php elseif ($emailStatus === 'pending'): ?>
                                        <span class="badge bg-warning text-dark mb-2">En attente de vérification</span>
                                        <form method="POST" action="<?= url('/auth/mfa/disable') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="email">
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Annuler la configuration ?')">
                                                Annuler
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('/auth/mfa/setup') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="method" value="email">
                                            <button type="submit" class="btn btn-primary">
                                                Activer Email
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($methods)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Aucune méthode MFA n'est actuellement activée. Nous vous recommandons d'en activer au moins une pour sécuriser votre compte.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection