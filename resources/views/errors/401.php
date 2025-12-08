<div class="container">
    <div class="error-code">
        <h1>401</h1>
    </div>
    <div class="col-md-8 offset-md-2">
        <h3>Non Authentifié</h3>
        <p class="sub-content">
            Vous devez être connecté pour accéder à cette page.
            Veuillez vous authentifier pour continuer.
        </p>
    </div>
    <div>
        <a class="btn btn-primary btn-lg" href="<?= url('/login') ?>">
            <i data-feather="log-in"></i> SE CONNECTER
        </a>
        <a class="btn btn-secondary btn-lg ms-2" href="<?= url('/') ?>">
            <i data-feather="home"></i> ACCUEIL
        </a>
    </div>
</div>
