@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Utilisateurs', 'url' => '/admin/users'],
    ['label' => 'Éditer']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <?php
        $card_title = "Éditer l'utilisateur : " . htmlspecialchars($user->username);
        component('card-start');
        ?>

        <form action="<?= url('/admin/users/' . $user->id . '/update') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($user->username) ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
                <small class="form-text text-muted">Laisser vide pour conserver le mot de passe actuel</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Rôles</label>
                <div class="border rounded p-3" style="background-color: #f8f9fa;">
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="roles[]"
                                    value="<?= $role->id ?>"
                                    id="role_<?= $role->id ?>"
                                    <?= in_array($role->id, $userRoleIds ?? []) ? 'checked' : '' ?>>
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
                    <i data-feather="save"></i> Mettre à jour
                </button>
                <a href="<?= url('/admin/users') ?>" class="btn btn-secondary">
                    <i data-feather="x"></i> Annuler
                </a>
                <a href="<?= url('/admin/users/' . $user->id . '/delete') ?>"
                    class="btn btn-danger float-end"
                    onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
                    <i data-feather="trash-2"></i> Supprimer
                </a>
            </div>
        </form>

        <?php component('card-end'); ?>
    </div>
</div>

@endsection