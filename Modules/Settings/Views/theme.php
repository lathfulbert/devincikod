@extends('backend.layouts.master')

@section('title', $title ?? 'Thème & Apparence')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Configuration', 'url' => '/admin/settings'],
        ['label' => 'Thème & Apparence']
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
        <div class="col-12">
            <?php
            component('card-start', ['card_title' => "Thème & Apparence"]);
            ?>

            <form action="<?= url('/admin/settings/theme/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="theme_primary_color" class="form-label">Couleur Primaire</label>
                        <input type="color" name="theme_primary_color" id="theme_primary_color" class="form-control form-control-color"
                               value="<?= htmlspecialchars($settings['theme_primary_color'] ?? '#007bff') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="theme_secondary_color" class="form-label">Couleur Secondaire</label>
                        <input type="color" name="theme_secondary_color" id="theme_secondary_color" class="form-control form-control-color"
                               value="<?= htmlspecialchars($settings['theme_secondary_color'] ?? '#6c757d') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="theme_font_family" class="form-label">Police</label>
                        <select name="theme_font_family" id="theme_font_family" class="form-select">
                            <option value="Arial" <?= ($settings['theme_font_family'] ?? '') === 'Arial' ? 'selected' : '' ?>>Arial</option>
                            <option value="Helvetica" <?= ($settings['theme_font_family'] ?? '') === 'Helvetica' ? 'selected' : '' ?>>Helvetica</option>
                            <option value="Roboto" <?= ($settings['theme_font_family'] ?? '') === 'Roboto' ? 'selected' : '' ?>>Roboto</option>
                            <option value="Open Sans" <?= ($settings['theme_font_family'] ?? '') === 'Open Sans' ? 'selected' : '' ?>>Open Sans</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="theme_mode" class="form-label">Mode</label>
                        <select name="theme_mode" id="theme_mode" class="form-select">
                            <option value="light" <?= ($settings['theme_mode'] ?? 'light') === 'light' ? 'selected' : '' ?>>Clair</option>
                            <option value="dark" <?= ($settings['theme_mode'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Sombre</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Enregistrer
                    </button>
                    <a href="<?= url('/admin/settings') ?>" class="btn btn-secondary">
                        <i data-feather="x"></i> Annuler
                    </a>
                    <button type="button" class="btn btn-warning" onclick="if(confirm('Réinitialiser le thème ?')) { document.getElementById('reset-form').submit(); }">
                        <i data-feather="refresh-cw"></i> Réinitialiser
                    </button>
                </div>
            </form>

            <!-- Hidden reset form -->
            <form id="reset-form" action="<?= url('/admin/settings/theme/reset') ?>" method="POST" style="display: none;">
                <?= csrf_field() ?>
            </form>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection
