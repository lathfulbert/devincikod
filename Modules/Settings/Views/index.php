@extends('backend.layouts.master')

@section('title', 'Paramètres Généraux')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Paramètres Généraux</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">
                        <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Paramètres</li>
                    <li class="breadcrumb-item active">Général</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i data-feather="check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_success']); endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i data-feather="alert-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_error']); endif; ?>

            <div class="card">
                <div class="card-header pb-0">
                    <h5>Configuration du Site</h5>
                    <span>Gérez les paramètres généraux de votre application</span>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs border-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#site" role="tab">
                                <i data-feather="globe"></i>Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#theme" role="tab">
                                <i data-feather="layout"></i>Thème
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#api" role="tab">
                                <i data-feather="key"></i>API
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#mail" role="tab">
                                <i data-feather="mail"></i>Email
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Site Settings -->
                        <div class="tab-pane fade show active" id="site" role="tabpanel">
                            <form action="<?= url('admin/settings/site/update') ?>" method="POST" class="mt-4">
                                <?= csrf_field() ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nom du Site</label>
                                            <input type="text" class="form-control" name="site_name"
                                                   value="<?= $settings['site_name'] ?? 'SunuFramework' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Langue par défaut</label>
                                            <select class="form-control" name="default_language">
                                                <option value="fr" <?= ($settings['default_language'] ?? 'fr') == 'fr' ? 'selected' : '' ?>>Français</option>
                                                <option value="en" <?= ($settings['default_language'] ?? 'fr') == 'en' ? 'selected' : '' ?>>English</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="site_description" rows="3"><?= $settings['site_description'] ?? '' ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Timezone</label>
                                            <select class="form-control" name="default_timezone">
                                                <option value="Africa/Dakar" <?= ($settings['default_timezone'] ?? 'Africa/Dakar') == 'Africa/Dakar' ? 'selected' : '' ?>>Africa/Dakar</option>
                                                <option value="Europe/Paris" <?= ($settings['default_timezone'] ?? 'Africa/Dakar') == 'Europe/Paris' ? 'selected' : '' ?>>Europe/Paris</option>
                                                <option value="America/New_York" <?= ($settings['default_timezone'] ?? 'Africa/Dakar') == 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Format de Date</label>
                                            <select class="form-control" name="date_format">
                                                <option value="Y-m-d" <?= ($settings['date_format'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                                <option value="d/m/Y" <?= ($settings['date_format'] ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                                <option value="m/d/Y" <?= ($settings['date_format'] ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="maintenance_mode"
                                                   id="maintenance_mode" value="1"
                                                   <?= ($settings['maintenance_mode'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="maintenance_mode">
                                                Mode Maintenance
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="save"></i> Enregistrer
                                </button>
                            </form>
                        </div>

                        <!-- Theme Settings -->
                        <div class="tab-pane fade" id="theme" role="tabpanel">
                            <form action="<?= url('admin/settings/theme/update') ?>" method="POST" class="mt-4">
                                <?= csrf_field() ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Thème</label>
                                            <select class="form-control" name="theme_mode">
                                                <option value="light" <?= ($settings['theme_mode'] ?? 'light') == 'light' ? 'selected' : '' ?>>Clair</option>
                                                <option value="dark" <?= ($settings['theme_mode'] ?? 'light') == 'dark' ? 'selected' : '' ?>>Sombre</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Couleur Primaire</label>
                                            <input type="color" class="form-control" name="primary_color"
                                                   value="<?= $settings['primary_color'] ?? '#7366ff' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Type de Sidebar</label>
                                            <select class="form-control" name="sidebar_type">
                                                <option value="compact" <?= ($settings['sidebar_type'] ?? 'compact') == 'compact' ? 'selected' : '' ?>>Compact</option>
                                                <option value="expanded" <?= ($settings['sidebar_type'] ?? 'compact') == 'expanded' ? 'selected' : '' ?>>Étendu</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="save"></i> Enregistrer
                                </button>
                            </form>
                        </div>

                        <!-- API Settings -->
                        <div class="tab-pane fade" id="api" role="tabpanel">
                            <form action="<?= url('admin/settings/api/update') ?>" method="POST" class="mt-4">
                                <?= csrf_field() ?>
                                <div class="alert alert-warning">
                                    <i data-feather="alert-triangle"></i>
                                    Ces clés API sont sensibles. Ne les partagez jamais.
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Clé API OpenAI</label>
                                            <input type="password" class="form-control" name="openai_api_key"
                                                   value="<?= $settings['openai_api_key'] ?? '' ?>"
                                                   placeholder="sk-...">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Clé API Google</label>
                                            <input type="password" class="form-control" name="google_api_key"
                                                   value="<?= $settings['google_api_key'] ?? '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="save"></i> Enregistrer
                                </button>
                            </form>
                        </div>

                        <!-- Mail Settings -->
                        <div class="tab-pane fade" id="mail" role="tabpanel">
                            <form action="<?= url('admin/settings/mail/update') ?>" method="POST" class="mt-4">
                                <?= csrf_field() ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Driver Mail</label>
                                            <select class="form-control" name="mail_driver">
                                                <option value="smtp" <?= ($settings['mail_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' ?>>SMTP</option>
                                                <option value="sendmail" <?= ($settings['mail_driver'] ?? 'smtp') == 'sendmail' ? 'selected' : '' ?>>Sendmail</option>
                                                <option value="mailgun" <?= ($settings['mail_driver'] ?? 'smtp') == 'mailgun' ? 'selected' : '' ?>>Mailgun</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Hôte SMTP</label>
                                            <input type="text" class="form-control" name="mail_host"
                                                   value="<?= $settings['mail_host'] ?? 'smtp.mailtrap.io' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Port SMTP</label>
                                            <input type="number" class="form-control" name="mail_port"
                                                   value="<?= $settings['mail_port'] ?? '2525' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Utilisateur SMTP</label>
                                            <input type="text" class="form-control" name="mail_username"
                                                   value="<?= $settings['mail_username'] ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Mot de passe SMTP</label>
                                            <input type="password" class="form-control" name="mail_password"
                                                   value="<?= $settings['mail_password'] ?? '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="save"></i> Enregistrer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endsection
