<!-- Language Selector Component -->
<!-- Usage: <?php component('language-selector') ?> -->

<div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i data-feather="globe"></i> <?= strtoupper(app_locale()) ?>
    </button>
    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
        <?php foreach (supported_locales() as $locale): ?>
            <li>
                <a class="dropdown-item <?= app_locale() === $locale ? 'active' : '' ?>"
                    href="?lang=<?= $locale ?>">
                    <?php
                    $localeNames = [
                        'fr' => '🇫🇷 Français',
                        'en' => '🇬🇧 English',
                        'ar' => '🇸🇦 العربية'
                    ];
                    echo $localeNames[$locale] ?? strtoupper($locale);
                    ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>