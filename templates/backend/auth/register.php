@extends('auth.layout')

@section('content')

<div class="auth-card">
    <div class="auth-logo">
        <h1><i data-feather="user-plus"></i> Inscription</h1>
        <p>Créez votre compte</p>
    </div>

    <?php component('alert') ?>

    <form method="POST" action="<?= url('/register') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="username" class="form-label">
                <i data-feather="user" style="width: 16px; height: 16px;"></i> Nom d'utilisateur *
            </label>
            <input
                type="text"
                name="username"
                id="username"
                class="form-control <?= has_error('username') ? 'is-invalid' : '' ?>"
                placeholder="Choisissez un nom d'utilisateur"
                autofocus
                value="<?= escape(old('username')) ?>">
            <?php component('error', ['field' => 'username']) ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">
                <i data-feather="mail" style="width: 16px; height: 16px;"></i> Email *
            </label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>"
                placeholder="votre@email.com"
                value="<?= escape(old('email')) ?>">
            <?php component('error', ['field' => 'email']) ?>
        </div>

        <div class="mb-3">
            <label for="first_name" class="form-label">
                <i data-feather="user" style="width: 16px; height: 16px;"></i> Prénom
            </label>
            <input
                type="text"
                name="first_name"
                id="first_name"
                class="form-control <?= has_error('first_name') ? 'is-invalid' : '' ?>"
                placeholder="Votre prénom"
                value="<?= escape(old('first_name')) ?>">
            <?php component('error', ['field' => 'first_name']) ?>
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">
                <i data-feather="user" style="width: 16px; height: 16px;"></i> Nom
            </label>
            <input
                type="text"
                name="last_name"
                id="last_name"
                class="form-control <?= has_error('last_name') ? 'is-invalid' : '' ?>"
                placeholder="Votre nom"
                value="<?= escape(old('last_name')) ?>">
            <?php component('error', ['field' => 'last_name']) ?>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                <i data-feather="lock" style="width: 16px; height: 16px;"></i> Mot de passe *
            </label>
            <input
                type="password"
                name="password"
                id="password"
                class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>"
                placeholder="Minimum 8 caractères">
            <?php component('error', ['field' => 'password']) ?>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                <i data-feather="lock" style="width: 16px; height: 16px;"></i> Confirmer le mot de passe *
            </label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="form-control"
                placeholder="Répétez le mot de passe">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="terms" id="terms">
            <label class="form-check-label" for="terms">
                J'accepte les <a href="#" class="text-link">conditions d'utilisation</a>
            </label>
            <?php if (has_error('terms')): ?>
                <div class="invalid-feedback d-block">
                    <?= error('terms') ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-login mt-3">
            <i data-feather="user-plus" style="width: 18px; height: 18px;"></i>
            S'inscrire
        </button>
    </form>

    <div class="divider">
        <span><i data-feather="more-horizontal" style="width: 16px; height: 16px;"></i></span>
    </div>

    <div class="text-center">
        <p class="mb-0">
            Vous avez déjà un compte ?
            <a href="<?= url('/login') ?>" class="text-link">
                <i data-feather="log-in" style="width: 14px; height: 14px;"></i>
                Se connecter
            </a>
        </p>
    </div>
</div>

<div class="text-center mt-3">
    <small style="color: rgba(255,255,255,0.8);">
        © <?= date('Y') ?> SunuFramework - Tous droits réservés
    </small>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation d'entrée
        const card = document.querySelector('.auth-card');
        if (card) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        }

        // Feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Validation en temps réel du mot de passe
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');

        if (password && passwordConfirm) {
            passwordConfirm.addEventListener('input', function() {
                if (password.value !== passwordConfirm.value) {
                    passwordConfirm.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    passwordConfirm.setCustomValidity('');
                }
            });
        }
    });
</script>
@endsection