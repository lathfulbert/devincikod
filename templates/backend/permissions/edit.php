@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Permissions', 'url' => '/admin/permissions'],
        ['label' => 'Éditer']
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
            $card_title = "Éditer la permission : " . htmlspecialchars($permission->name);
            component('card-start');
            ?>

            <form action="<?= url('/admin/permissions/' . $permission->id . '/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nom de la permission <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($permission->name) ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($permission->description ?? '') ?></textarea>
                    <small class="form-text text-muted">Description optionnelle de la permission</small>
                </div>

                <div class="mb-3">
                    <label for="module_name" class="form-label">Module</label>
                    <input type="text" name="module_name" id="module_name" class="form-control" value="<?= htmlspecialchars($permission->module_name ?? '') ?>">
                    <small class="form-text text-muted">Nom du module associé (optionnel)</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Mettre à jour
                    </button>
                    <a href="<?= url('/admin/permissions') ?>" class="btn btn-secondary">
                        <i data-feather="x"></i> Annuler
                    </a>
                    <a href="<?= url('/admin/permissions/' . $permission->id . '/delete') ?>"
                        class="btn btn-danger float-end"
                        onclick="return confirm('Supprimer définitivement cette permission ?')">
                        <i data-feather="trash-2"></i> Supprimer
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