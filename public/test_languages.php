<?php

/**
 * Test de diagnostic pour le système de langues
 */

echo "<h1>Test du Système de Langues</h1>";

echo "<h2>1. Test direct du fichier de configuration</h2>";
$languagesFile = __DIR__ . '/../config/languages.php';
echo "<p>Fichier existe: " . (file_exists($languagesFile) ? 'OUI' : 'NON') . "</p>";

if (file_exists($languagesFile)) {
    $directLoad = require $languagesFile;
    echo "<p>Nombre de langues chargées directement: " . count($directLoad) . "</p>";
    echo "<pre>";
    print_r($directLoad);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>ERREUR: Le fichier de configuration n'existe pas!</p>";
}

echo "<h2>2. Test avec autoload et helpers</h2>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../Core/Support/helpers.php';

    echo "<p>Autoload chargé: OUI</p>";

    // Initialiser l'application
    $basePath = dirname(__DIR__);
    $_ENV['APP_BASE_PATH'] = $basePath;

    $app = \App\Core\Application::getInstance();
    echo "<p>Application instance créée: OUI</p>";

    $app->boot();
    echo "<p>Application bootée: OUI</p>";

    echo "<h2>3. Test via la fonction config()</h2>";
    $configLanguages = config('languages');
    echo "<p>Type retourné: " . gettype($configLanguages) . "</p>";
    echo "<p>Nombre de langues via config(): " . (is_array($configLanguages) ? count($configLanguages) : 'N/A') . "</p>";
    echo "<pre>";
    print_r($configLanguages);
    echo "</pre>";

    echo "<h2>4. Test de la configuration app</h2>";
    $supportedLocales = config('app.supported_locales');
    $defaultLocale = config('app.locale');
    $fallbackLocale = config('app.fallback_locale');

    echo "<p>Locales supportées: </p><pre>";
    print_r($supportedLocales);
    echo "</pre>";
    echo "<p>Locale par défaut: " . $defaultLocale . "</p>";
    echo "<p>Locale de fallback: " . $fallbackLocale . "</p>";

    echo "<h2>5. Test du contrôleur</h2>";
    $controller = new \Modules\Settings\Controllers\LanguageController();

    // Utiliser la réflexion pour accéder à la méthode privée
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getAvailableLanguages');
    $method->setAccessible(true);
    $availableLanguages = $method->invoke($controller);

    echo "<p>Nombre de langues disponibles: " . (is_array($availableLanguages) ? count($availableLanguages) : 'N/A') . "</p>";
    echo "<pre>";
    print_r($availableLanguages);
    echo "</pre>";

    echo "<h2>6. Simulation de la méthode index()</h2>";
    $languages = [];
    foreach ($availableLanguages as $code => $lang) {
        $languages[$code] = array_merge($lang, [
            'is_active' => in_array($code, $supportedLocales ?? ['fr', 'en', 'ar']),
            'is_default' => $code === $defaultLocale,
            'is_fallback' => $code === $fallbackLocale,
            'has_file' => file_exists($app->getBasePath() . "/languages/{$code}.json")
        ]);
    }

    echo "<p>Nombre de langues finales: " . count($languages) . "</p>";
    echo "<pre>";
    print_r($languages);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>ERREUR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
