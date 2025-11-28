@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Permissions', 'url' => '/admin/permissions'],
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
            $card_title = "Créer une nouvelle permission";
            component('card-start');
            ?>

            <form action="<?= url('/admin/permissions/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nom de la permission <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= old('name') ?>" required autofocus>
                    <small class="form-text text-muted">Exemple: users.create, posts.edit, etc.</small>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?= old('description') ?></textarea>
                    <small class="form-text text-muted">Description optionnelle de la permission</small>
                </div>

                <div class="mb-3">
                    <label for="module_name" class="form-label">Module</label>
                    <input type="text" name="module_name" id="module_name" class="form-control" value="<?= old('module_name') ?>">
                    <small class="form-text text-muted">Nom du module associé (optionnel)</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Créer la permission
                    </button>
                    <a href="<?= url('/admin/permissions') ?>" class="btn btn-secondary">
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