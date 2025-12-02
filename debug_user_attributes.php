<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

$_SESSION['user_id'] = 1;

echo "=== Debug des attributs du modèle User ===\n\n";

$userId = $_SESSION['user_id'];
$user = \Modules\Users\Models\User::find($userId);

echo "1. Accès direct aux propriétés:\n";
echo "   \$user->api_key = " . var_export($user->api_key, true) . "\n";
echo "   \$user->api_key_created_at = " . var_export($user->api_key_created_at, true) . "\n";
echo "   \$user->username = " . var_export($user->username, true) . "\n";

echo "\n2. Test de !empty():\n";
echo "   !empty(\$user->api_key) = " . var_export(!empty($user->api_key), true) . "\n";
echo "   !empty(\$user->username) = " . var_export(!empty($user->username), true) . "\n";

echo "\n3. Utilisation de Reflection pour voir les attributs internes:\n";
$reflection = new ReflectionClass($user);
$attributesProperty = null;

// Chercher la propriété attributes
foreach ($reflection->getProperties() as $prop) {
    if ($prop->getName() === 'attributes') {
        $prop->setAccessible(true);
        $attributesProperty = $prop;
        break;
    }
}

if ($attributesProperty) {
    $attributes = $attributesProperty->getValue($user);
    echo "   Contenu de \$attributes:\n";
    foreach ($attributes as $key => $value) {
        $displayValue = is_string($value) && strlen($value) > 30 ? substr($value, 0, 30) . "..." : $value;
        echo "     - $key => " . var_export($displayValue, true) . "\n";
    }

    echo "\n4. Vérification des clés api:\n";
    echo "   isset(\$attributes['api_key']) = " . var_export(isset($attributes['api_key']), true) . "\n";
    echo "   array_key_exists('api_key', \$attributes) = " . var_export(array_key_exists('api_key', $attributes), true) . "\n";

    if (isset($attributes['api_key'])) {
        echo "   \$attributes['api_key'] = '" . substr($attributes['api_key'], 0, 20) . "...'\n";
    }
}

echo "\n5. Test de la condition de la vue:\n";
$hasApiKey = !empty($user->api_key);
echo "   \$hasApiKey = !empty(\$user->api_key) = " . var_export($hasApiKey, true) . "\n";

if ($hasApiKey) {
    echo "   ✓ La vue DEVRAIT afficher la clé\n";
} else {
    echo "   ✗ La vue affichera 'Aucune Clé API'\n";
}
