@extends('auth.layout')

@section('content')

<div class="auth-card">
    <div class="auth-logo">
        <h1><i data-feather="zap"></i> SunuFramework</h1>
        <p>Connectez-vous à votre compte</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger d-flex align-items-center">
            <i data-feather="alert-circle" class="me-2" style="width: 20px; height: 20px;"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i data-feather="check-circle" class="me-2" style="width: 20px; height: 20px;"></i>
            <?= htmlspecialchars($_SESSION['flash']['success']) ?>
        </div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= url('/login') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="username" class="form-label">
                <i data-feather="user" style="width: 16px; height: 16px;"></i> Nom d'utilisateur
            </label>
            <input
                type="text"
                name="username"
                id="username"
                class="form-control"
                placeholder="Entrez votre nom d'utilisateur"
                required
                autofocus
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            <small class="text-muted">Par défaut: admin</small>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                <i data-feather="lock" style="width: 16px; height: 16px;"></i> Mot de passe
            </label>
            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                placeholder="Entrez votre mot de passe"
                required>
            <small class="text-muted">Par défaut: password</small>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">
                Se souvenir de moi
            </label>
        </div>

        <button type="submit" class="btn btn-login mt-3">
            <i data-feather="log-in" style="width: 18px; height: 18px;"></i>
            Se connecter
        </button>
    </form>

    <div class="divider">
        <span><i data-feather="more-horizontal" style="width: 16px; height: 16px;"></i></span>
    </div>

    <div class="text-center">
        <a href="<?= url('/password/reset') ?>" class="text-link">
            <i data-feather="help-circle" style="width: 14px; height: 14px;"></i>
            Mot de passe oublié ?
        </a>
    </div>

    <div class="text-center mt-3">
        <small class="text-muted">
            <i data-feather="shield" style="width: 14px; height: 14px;"></i>
            Connexion sécurisée avec chiffrement SSL
        </small>
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
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);

        // Feather icons
        feather.replace();
    });
</script>
@endsection