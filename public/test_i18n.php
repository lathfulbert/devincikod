<?php
/**
 * Page de test pour l'intégration I18n avec le template AbckOffice
 *
 * URL: http://votre-domaine/test_i18n.php
 *
 * Cette page permet de tester:
 * - Le changement de langue via le menu
 * - L'affichage des traductions
 * - La persistance de la langue en session
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

$app = new Application(dirname(__DIR__));
$app->boot();

// Récupérer la locale actuelle
$locale = app_locale();
$supportedLocales = supported_locales();
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= trans('app.name') ?> - Test I18n</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= url() ?>/assets/css/bootstrap.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= url() ?>/assets/css/fontawesome.css">
    <!-- Themify icon -->
    <link rel="stylesheet" href="<?= url() ?>/assets/css/themify.css">
    <!-- Flag Icons -->
    <link rel="stylesheet" href="<?= url() ?>/assets/css/flag-icon.css">
    <!-- Feather Icons -->
    <link rel="stylesheet" href="<?= url() ?>/assets/css/feather-icon.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= url() ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= url() ?>/assets/css/responsive.css">

    <style>
        body {
            background: #f5f5f5;
            padding: 50px 0;
        }
        .test-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .test-header {
            border-bottom: 2px solid #7366ff;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .info-row {
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #7366ff;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .translation-demo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .badge-locale {
            font-size: 16px;
            padding: 10px 20px;
        }
        .test-section {
            margin-top: 30px;
        }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 3px;
            color: #e83e8c;
        }
    </style>
</head>
<body>
    <!-- Header avec menu langue -->
    <?php include view_path('backend/layouts/header') ?>

    <div class="container">
        <!-- En-tête de test -->
        <div class="test-card">
            <div class="test-header">
                <h1>
                    <i class="icon-world" style="color: #7366ff;"></i>
                    Test d'intégration I18n - <?= trans('app.name') ?>
                </h1>
                <p class="text-muted mb-0">
                    <?= trans('app.tagline') ?>
                </p>
            </div>

            <!-- Informations sur la locale actuelle -->
            <div class="info-row">
                <strong>Locale actuelle:</strong>
                <span class="badge badge-primary badge-locale"><?= strtoupper($locale) ?></span>
                <span class="ms-3">
                    <?php
                    $localeNames = [
                        'fr' => '🇫🇷 Français',
                        'en' => '🇬🇧 English',
                        'ar' => '🇸🇦 العربية'
                    ];
                    echo $localeNames[$locale] ?? $locale;
                    ?>
                </span>
            </div>

            <div class="info-row">
                <strong>Langues supportées:</strong>
                <?php foreach ($supportedLocales as $loc): ?>
                    <span class="badge <?= $loc === $locale ? 'badge-success' : 'badge-secondary' ?> ms-2">
                        <?= strtoupper($loc) ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <div class="info-row">
                <strong>Locale de fallback:</strong>
                <span class="badge badge-warning"><?= strtoupper(config('app.fallback_locale')) ?></span>
            </div>
        </div>

        <!-- Démonstration des traductions -->
        <div class="test-card">
            <div class="test-header">
                <h2><?= trans('dashboard.title') ?></h2>
            </div>

            <div class="translation-demo">
                <h3><?= trans('dashboard.welcome') ?></h3>
                <p><?= trans('auth.welcome', ['name' => 'Jean Dupont']) ?></p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h4><?= trans('navigation.home') ?></h4>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong><?= trans('navigation.dashboard') ?>:</strong>
                            <?= trans('dashboard.overview') ?>
                        </li>
                        <li class="list-group-item">
                            <strong><?= trans('navigation.users') ?>:</strong>
                            <?= trans('users.title') ?>
                        </li>
                        <li class="list-group-item">
                            <strong><?= trans('navigation.roles') ?>:</strong>
                            <?= trans('roles.title') ?>
                        </li>
                        <li class="list-group-item">
                            <strong><?= trans('navigation.permissions') ?>:</strong>
                            <?= trans('permissions.title') ?>
                        </li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <h4><?= trans('actions.save') ?> / <?= trans('actions.edit') ?></h4>
                    <div class="btn-group-vertical w-100" role="group">
                        <button class="btn btn-primary mb-2">
                            <i class="fa fa-save"></i> <?= trans('actions.save') ?>
                        </button>
                        <button class="btn btn-info mb-2">
                            <i class="fa fa-edit"></i> <?= trans('actions.edit') ?>
                        </button>
                        <button class="btn btn-danger mb-2">
                            <i class="fa fa-trash"></i> <?= trans('actions.delete') ?>
                        </button>
                        <button class="btn btn-success mb-2">
                            <i class="fa fa-plus"></i> <?= trans('actions.create') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test de pluralisation -->
        <div class="test-card">
            <div class="test-header">
                <h2>Test de pluralisation</h2>
            </div>

            <div class="alert alert-info">
                <strong>Note:</strong> La pluralisation fonctionne avec la syntaxe
                <code>singulier|pluriel</code> dans les fichiers de traduction.
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Traduction</th>
                        <th>Avec remplacement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                    <tr>
                        <td><strong><?= $i ?></strong></td>
                        <td><?= trans_choice('users.count', $i) ?></td>
                        <td><?= trans_choice('users.count', $i, ['count' => $i]) ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- Messages système -->
        <div class="test-card">
            <div class="test-header">
                <h2><?= trans('messages.success') ?></h2>
            </div>

            <div class="test-section">
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?= trans('messages.saved') ?>
                </div>
                <div class="alert alert-primary">
                    <i class="fa fa-info-circle"></i> <?= trans('messages.updated') ?>
                </div>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?= trans('messages.error') ?>
                </div>
                <div class="alert alert-warning">
                    <i class="fa fa-question-circle"></i> <?= trans('messages.confirm_delete') ?>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="test-card">
            <div class="test-header">
                <h2>Instructions de test</h2>
            </div>

            <ol class="test-section">
                <li class="mb-3">
                    <strong>Cliquez sur le menu langue</strong> en haut à droite de la page
                    (icône de drapeau avec la langue actuelle)
                </li>
                <li class="mb-3">
                    <strong>Sélectionnez une langue différente</strong> dans le dropdown
                </li>
                <li class="mb-3">
                    <strong>La page va recharger</strong> automatiquement avec la nouvelle langue
                </li>
                <li class="mb-3">
                    <strong>Vérifiez que tous les textes</strong> sont traduits dans la nouvelle langue
                </li>
                <li class="mb-3">
                    <strong>Testez la persistance</strong> en naviguant vers d'autres pages -
                    la langue doit rester celle que vous avez sélectionnée
                </li>
            </ol>

            <div class="alert alert-success">
                <strong>✓ Intégration réussie!</strong> Le menu langue du template AbckOffice
                fonctionne maintenant avec le système I18n natif de SunuFramework2.
            </div>
        </div>

        <!-- Informations techniques -->
        <div class="test-card">
            <div class="test-header">
                <h2>Informations techniques</h2>
            </div>

            <div class="row test-section">
                <div class="col-md-6">
                    <h5>Fichiers modifiés</h5>
                    <ul>
                        <li><code>templates/backend/components/language-selector.php</code></li>
                        <li><code>templates/backend/layouts/header.php</code></li>
                        <li><code>templates/backend/layouts/script.php</code></li>
                        <li><code>public/assets/js/i18n-language-selector.js</code></li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <h5>Helpers utilisés</h5>
                    <ul>
                        <li><code>trans('key')</code> - Traduction simple</li>
                        <li><code>trans('key', ['param' => 'value'])</code> - Avec paramètres</li>
                        <li><code>trans_choice('key', count)</code> - Pluralisation</li>
                        <li><code>app_locale()</code> - Locale actuelle</li>
                        <li><code>supported_locales()</code> - Langues supportées</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <strong>Documentation complète:</strong> Consultez le fichier
                <code>INTEGRATION_I18N.md</code> à la racine du projet pour plus de détails.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5 mb-3">
        <p class="text-muted">
            <?= trans('app.name') ?> &copy; <?= date('Y') ?> -
            <?= trans('common.by') ?> DevinciKod
        </p>
    </footer>

    <!-- Scripts -->
    <script src="<?= url() ?>/assets/js/jquery-3.5.1.min.js"></script>
    <script src="<?= url() ?>/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?= url() ?>/assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="<?= url() ?>/assets/js/icons/feather-icon/feather-icon.js"></script>
    <script src="<?= url() ?>/assets/js/scrollbar/simplebar.min.js"></script>
    <script src="<?= url() ?>/assets/js/scrollbar/custom.js"></script>
    <script src="<?= url() ?>/assets/js/config.js"></script>
    <script src="<?= url() ?>/assets/js/sidebar-menu.js"></script>
    <script src="<?= url() ?>/assets/js/script.js"></script>
    <script src="<?= url() ?>/assets/js/i18n-language-selector.js"></script>

    <script>
        // Initialiser Feather Icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        console.log('🌍 I18n Test Page Loaded');
        console.log('Current Locale:', '<?= $locale ?>');
        console.log('Supported Locales:', <?= json_encode($supportedLocales) ?>);
    </script>
</body>
</html>
