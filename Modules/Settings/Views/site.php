@extends('backend.layouts.master')

@section('title', $title ?? 'Paramètres du Site')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Configuration', 'url' => '/admin/settings'],
        ['label' => 'Paramètres du site']
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
            $card_title = "Paramètres du Site";
            component('card-start');
            ?>

            <form action="<?= url('/admin/settings/site/update') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="site_name" class="form-label">Nom du Site</label>
                        <input type="text" name="site_name" id="site_name" class="form-control"
                               value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="site_tagline" class="form-label">Slogan</label>
                        <input type="text" name="site_tagline" id="site_tagline" class="form-control"
                               value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="site_email" class="form-label">Email du Site</label>
                        <input type="email" name="site_email" id="site_email" class="form-control"
                               value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="site_url" class="form-label">URL du Site</label>
                        <input type="url" name="site_url" id="site_url" class="form-control"
                               value="<?= htmlspecialchars($settings['site_url'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="site_description" class="form-label">Description</label>
                    <textarea name="site_description" id="site_description" class="form-control" rows="3"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">Logos et Icônes</h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="logo" class="form-label">Logo (Light Mode)</label>
                        <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <div class="mt-2">
                                <img src="<?= site_logo() ?>" alt="Logo" style="max-height: 50px;">
                                <small class="d-block text-muted">Chemin : <?= htmlspecialchars($settings['site_logo']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="logo_dark" class="form-label">Logo (Dark Mode)</label>
                        <input type="file" name="logo_dark" id="logo_dark" class="form-control" accept="image/*">
                        <?php if (!empty($settings['site_logo_dark'])): ?>
                            <div class="mt-2">
                                <img src="<?= site_logo_dark() ?>" alt="Logo Dark" style="max-height: 50px;">
                                <small class="d-block text-muted">Chemin : <?= htmlspecialchars($settings['site_logo_dark']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="logo_icon" class="form-label">Logo Icon (pour sidebar réduite)</label>
                        <input type="file" name="logo_icon" id="logo_icon" class="form-control" accept="image/*">
                        <?php if (!empty($settings['site_logo_icon'])): ?>
                            <div class="mt-2">
                                <img src="<?= site_logo_icon() ?>" alt="Logo Icon" style="max-height: 40px;">
                                <small class="d-block text-muted">Chemin : <?= htmlspecialchars($settings['site_logo_icon']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="favicon" class="form-label">Favicon</label>
                        <input type="file" name="favicon" id="favicon" class="form-control" accept="image/*">
                        <?php if (!empty($settings['site_favicon'])): ?>
                            <div class="mt-2">
                                <img src="<?= site_favicon() ?>" alt="Favicon" style="max-height: 32px;">
                                <small class="d-block text-muted">Chemin : <?= htmlspecialchars($settings['site_favicon']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Enregistrer
                    </button>
                    <a href="<?= url('/admin/settings') ?>" class="btn btn-secondary">
                        <i data-feather="x"></i> Annuler
                    </a>
                </div>
            </form>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection
