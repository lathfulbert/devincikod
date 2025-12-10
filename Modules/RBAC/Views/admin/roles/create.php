@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Rôles', 'url' => '/admin/roles'],
        ['label' => 'Créer']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <?php
            component('card-start', ['card_title' => "Créer un nouveau rôle"]);
            ?>

            <form action="<?= url('/admin/roles/store') ?>
" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required autofocus>
                    <small class="form-text text-muted">Exemple: Administrateur, Éditeur, etc.</small>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" id="slug" class="form-control" required>
                    <small class="form-text text-muted">Exemple: admin, editor, etc. (en minuscules, sans espaces)</small>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    <small class="form-text text-muted">Description optionnelle du rôle</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <div class="border rounded p-3" style="background-color: #f8f9fa;">
                        <?php if (!empty($permissions)): ?>
                            <?php foreach ($permissions as $permission): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $permission->id ?>" id="perm_<?= $permission->id ?>">
                                    <label class="form-check-label" for="perm_<?= $permission->id ?>">
                                        <strong><?= htmlspecialchars($permission->name) ?></strong>
                                        <?php if (!empty($permission->description)): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($permission->description) ?></small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0"><i data-feather="alert-circle"></i> Aucune permission disponible</p>
                        <?php endif; ?>
                    </div>
                    <small class="form-text text-muted">Sélectionnez les permissions pour ce rôle</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Créer le rôle
                    </button>
                    <a href="<?= url('/admin/roles') ?>" class="btn btn-secondary">
                        <i data-feather="x"></i> Annuler
                    </a>
                </div>
            </form>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection