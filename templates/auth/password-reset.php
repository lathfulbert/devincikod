@extends('auth.layout')

@section('content')

<div class="auth-card">
    <div class="auth-logo">
        <h1><i data-feather="key"></i> Réinitialiser</h1>
        <p>Récupérez l'accès à votre compte</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i data-feather="check-circle" class="me-2" style="width: 20px; height: 20px;"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger d-flex align-items-center">
            <i data-feather="alert-circle" class="me-2" style="width: 20px; height: 20px;"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!isset($success)): ?>
        <form method="POST" action="<?= url('/password/reset') ?>">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label for="email" class="form-label">
                    <i data-feather="mail" style="width: 16px; height: 16px;"></i> Adresse email
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    placeholder="votre@email.com"
                    required
                    autofocus
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <small class="text-muted">
                    Nous vous enverrons un lien de réinitialisation
                </small>
            </div>

            <button type="submit" class="btn btn-login mt-2">
                <i data-feather="send" style="width: 18px; height: 18px;"></i>
                Envoyer le lien
            </button>
        </form>
    <?php endif; ?>

    <div class="divider">
        <span><i data-feather="more-horizontal" style="width: 16px; height: 16px;"></i></span>
    </div>

    <div class="text-center">
        <a href="<?= url('/login') ?>" class="text-link">
            <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
            Retour à la connexion
        </a>
    </div>

    <div class="text-center mt-3">
        <small class="text-muted">
            <i data-feather="info" style="width: 14px; height: 14px;"></i>
            Besoin d'aide ? Contactez l'administrateur
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