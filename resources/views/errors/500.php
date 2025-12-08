<div class="container">
    <div class="error-code">
        <h1>500</h1>
    </div>
    <div class="col-md-8 offset-md-2">
        <h3>Erreur Serveur</h3>
        <p class="sub-content">
            Le serveur a rencontré une erreur interne et n'a pas pu traiter votre requête.
            Veuillez réessayer plus tard ou contacter l'administrateur si le problème persiste.
        </p>
    </div>
    <div>
        <a class="btn btn-primary btn-lg" href="<?= url('/admin/dashboard') ?>">
            <i data-feather="home"></i> RETOUR À L'ACCUEIL
        </a>
    </div>
    <?php if (isset($message) && (getenv('APP_DEBUG') === 'true')): ?>
        <div class="col-md-10 offset-md-1 mt-5">
            <div class="alert alert-danger text-left">
                <h5>Détails de l'erreur (Mode Debug):</h5>
                <pre style="color: #fff; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 5px; text-align: left; white-space: pre-wrap;"><?= htmlspecialchars($message) ?></pre>
            </div>
        </div>
    <?php endif; ?>
</div>
