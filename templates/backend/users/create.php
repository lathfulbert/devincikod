@extends('admin.layout')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Utilisateurs', 'url' => '/admin/users'],
    ['label' => 'Créer']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <?php
        $card_title = "Créer un nouvel utilisateur";
        component('card-start');
        ?>

        <form action="<?= url('/admin/users/store') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                <input type="text" name="username" id="username" class="form-control" required autofocus>
                <small class="form-text text-muted">Choisissez un nom d'utilisateur unique</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password">
                <small class="form-text text-muted">Minimum 8 caractères recommandé</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Rôles</label>
                <div class="border rounded p-3" style="background-color: #f8f9fa;">
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $role->id ?>" id="role_<?= $role->id ?>">
                                <label class="form-check-label" for="role_<?= $role->id ?>">
                                    <strong><?= htmlspecialchars($role->name) ?></strong>
                                    <?php if (!empty($role->description)): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($role->description) ?></small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0"><i data-feather="alert-circle"></i> Aucun rôle disponible</p>
                    <?php endif; ?>
                </div>
                <small class="form-text text-muted">Sélectionnez un ou plusieurs rôles pour cet utilisateur</small>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save"></i> Créer l'utilisateur
                </button>
                <a href="<?= url('/admin/users') ?>" class="btn btn-secondary">
                    <i data-feather="x"></i> Annuler
                </a>
            </div>
        </form>

        <?php component('card-end'); ?>
    </div>
</div>

@endsection