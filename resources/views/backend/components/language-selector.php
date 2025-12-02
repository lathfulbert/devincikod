<?php
// Charger la configuration des langues depuis le fichier centralisé
$localeConfig = config('languages');

// Si le fichier n'a pas été chargé, le charger manuellement
if (empty($localeConfig)) {
    $configFile = app()->getBasePath() . '/config/languages.php';
    if (file_exists($configFile)) {
        $localeConfig = require $configFile;
        app()->config->set('languages', $localeConfig);
    } else {
        $localeConfig = [];
    }
}

$currentLocale = app_locale();
$supportedLocales = supported_locales();

// Configuration de la langue courante avec fallback
$currentConfig = $localeConfig[$currentLocale] ?? [
    'name' => strtoupper($currentLocale),
    'native_name' => strtoupper($currentLocale),
    'flag' => 'flag-icon-us',
    'short' => strtoupper($currentLocale)
];
?>



<div class="translate_wrapper" style="position: relative; z-index: 1000;">
    <div class="current_lang">
        <div class="lang">
            <i class="flag-icon <?= $currentConfig['flag'] ?>"></i>
            <span class="lang-txt"><?= $currentConfig['short'] ?></span>
        </div>
    </div>
    <div class="more_lang">
        <?php foreach ($supportedLocales as $locale): ?>
            <?php
            $config = $localeConfig[$locale] ?? [
                'name' => strtoupper($locale),
                'flag' => 'flag-icon-us',
                'short' => strtoupper($locale)
            ];
            $isSelected = ($locale === $currentLocale) ? 'selected' : '';
            ?>
            <div class="lang <?= $isSelected ?>" data-value="<?= $locale ?>" data-url="<?= url('?lang=' . $locale) ?>">
                <i class="flag-icon <?= $config['flag'] ?>"></i>
                <span class="lang-txt"><?= $config['name'] ?><?php if (isset($config['suffix'])): ?><span> <?= $config['suffix'] ?></span><?php endif; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Le script JavaScript est chargé dans le layout principal -->
<!-- Voir: public/assets/js/i18n-language-selector.js -->