<?php

/**
 * Exemple de Validation de Formulaires
 * 
 * Ce fichier démontre l'utilisation du système de validation
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Validation\Validator;

// Simuler des données POST
$_POST = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => '25',
    'password' => 'secret123',
    'password_confirmation' => 'secret123',
    'website' => 'https://example.com',
    'role' => 'admin'
];

echo "=== Exemple de Validation de Formulaires ===\n\n";

// ==================== Exemple 1: Validation réussie ====================
echo "Exemple 1: Validation réussie\n";
echo str_repeat('-', 50) . "\n";

try {
    $validator = validator($_POST, [
        'name' => 'required|min:3|max:255',
        'email' => 'required|email',
        'age' => 'required|integer|min:18',
        'password' => 'required|min:8|confirmed',
        'website' => 'url',
        'role' => 'required|in:admin,user,moderator'
    ]);

    if ($validator->passes()) {
        echo "✓ Validation réussie!\n";
        echo "Données validées:\n";
        print_r($validator->validated());
    }
} catch (\Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// ==================== Exemple 2: Validation échouée ====================
echo "Exemple 2: Validation échouée\n";
echo str_repeat('-', 50) . "\n";

$invalidData = [
    'name' => 'Jo', // Trop court (min:3)
    'email' => 'invalid-email', // Email invalide
    'age' => '15', // Trop jeune (min:18)
    'password' => 'short', // Mot de passe trop court (min:8)
    'password_confirmation' => 'different', // Confirmation ne correspond pas
    'website' => 'not-a-url', // URL invalide
    'role' => 'superadmin' // Pas dans la liste
];

$validator = validator($invalidData, [
    'name' => 'required|min:3|max:255',
    'email' => 'required|email',
    'age' => 'required|integer|min:18',
    'password' => 'required|min:8|confirmed',
    'website' => 'url',
    'role' => 'required|in:admin,user,moderator'
]);

if ($validator->fails()) {
    echo "✗ Validation échouée!\n";
    echo "Erreurs:\n";

    foreach ($validator->errors()->all() as $field => $messages) {
        echo "  - {$field}:\n";
        foreach ($messages as $message) {
            echo "    • {$message}\n";
        }
    }
}

echo "\n";

// ==================== Exemple 3: Messages personnalisés ====================
echo "Exemple 3: Messages personnalisés\n";
echo str_repeat('-', 50) . "\n";

$validator = validator(['username' => ''], [
    'username' => 'required|min:5'
], [
    'username.required' => 'Le nom d\'utilisateur est obligatoire!',
    'username.min' => 'Le nom d\'utilisateur doit contenir au moins 5 caractères!'
]);

if ($validator->fails()) {
    echo "✗ Erreurs personnalisées:\n";
    foreach ($validator->errors()->flatten() as $error) {
        echo "  • {$error}\n";
    }
}

echo "\n";

// ==================== Exemple 4: Validation avec exception ====================
echo "Exemple 4: Validation avec exception\n";
echo str_repeat('-', 50) . "\n";

try {
    $data = validator(['email' => 'bad-email'], [
        'email' => 'required|email'
    ])->validate(); // Lève une exception si validation échoue

    echo "Données validées: " . json_encode($data) . "\n";
} catch (\App\Core\Validation\ValidationException $e) {
    echo "✗ ValidationException capturée!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Erreurs:\n";
    foreach ($e->errors()->flatten() as $error) {
        echo "  • {$error}\n";
    }
}

echo "\n";

// ==================== Exemple 5: Règles diverses ====================
echo "Exemple 5: Règles diverses\n";
echo str_repeat('-', 50) . "\n";

$testData = [
    'alpha_field' => 'OnlyLetters',
    'numeric_field' => '123.45',
    'date_field' => '2024-12-31',
    'boolean_field' => '1',
    'regex_field' => 'ABC123'
];

$validator = validator($testData, [
    'alpha_field' => 'alpha',
    'numeric_field' => 'numeric',
    'date_field' => 'date|before:2025-01-01',
    'boolean_field' => 'boolean',
    'regex_field' => 'regex:/^[A-Z0-9]+$/'
]);

if ($validator->passes()) {
    echo "✓ Toutes les règles diverses sont passées!\n";
    print_r($validator->validated());
} else {
    echo "✗ Certaines règles ont échoué\n";
    print_r($validator->errors()->all());
}

echo "\n=== Tests terminés ===\n";
