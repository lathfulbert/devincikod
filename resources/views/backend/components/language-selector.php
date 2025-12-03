<?php

use App\Core\I18n\LanguageManager;

$manager = LanguageManager::getInstance();
$currentLocale = $manager->getLocale();
$supportedLocales = $manager->getSupportedLocales();

// Configuration des drapeaux
$flags = [
    'fr' => ['flag' => 'flag-icon-fr', 'name' => 'Français', 'short' => 'FR'],
    'en' => ['flag' => 'flag-icon-us', 'name' => 'English', 'short' => 'EN'],
    'ar' => ['flag' => 'flag-icon-ae', 'name' => 'لعربية', 'short' => 'AR'],
];

$currentConfig = $flags[$currentLocale] ?? $flags['fr'];
?>

<div class="media profile-media">
    <div class="lang">
        <i class="flag-icon <?= $currentConfig['flag'] ?>"></i>
        <span class="lang-txt"><?= $currentConfig['short'] ?></span>
    </div>
</div>
<div class="onhover-show-div">
    <ul class="profile-dropdown">
        <?php foreach ($supportedLocales as $locale): ?>
            <?php
            $config = $flags[$locale] ?? ['flag' => 'flag-icon-us', 'name' => strtoupper($locale), 'short' => strtoupper($locale)];
            $isSelected = ($locale === $currentLocale) ? 'selected' : '';
            ?>
            <li>
                <a href="<?= url('/admin/i18n/set-locale?locale=' . $locale) ?>" class="<?= $isSelected ?>">
                    <i class="flag-icon <?= $config['flag'] ?>"></i>
                    <span><?= $config['name'] ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>