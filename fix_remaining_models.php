<?php

echo "🔧 Correction des modèles restants\n";
echo str_repeat("=", 70) . "\n\n";

$fixes = [
    'Modules/EmailMarketing/Models/EmailCampaign.php' => [
        'add_to_fillable' => ['updated_by'],
    ],
    'Modules/EmailMarketing/Models/EmailTemplate.php' => [
        'add_to_fillable' => ['updated_by'],
    ],
    'Modules/EmailMarketing/Models/Workflow.php' => [
        'add_to_fillable' => ['updated_by'],
    ],
    'Modules/Contacts/Models/Contact.php' => [
        'add_to_fillable' => ['created_by', 'updated_by', 'deleted_by'],
    ],
    'Modules/ApiKeys/Models/ApiKey.php' => [
        'create_fillable' => ['key', 'name', 'user_id', 'permissions', 'is_active', 'expires_at', 'created_by', 'updated_by', 'deleted_by'],
    ],
];

foreach ($fixes as $file => $actions) {
    $fullPath = __DIR__ . '/' . $file;

    if (!file_exists($fullPath)) {
        echo "⚠️  Fichier non trouvé: $file\n\n";
        continue;
    }

    echo "📝 $file\n";
    $content = file_get_contents($fullPath);
    $modified = false;

    // Add to existing fillable
    if (isset($actions['add_to_fillable'])) {
        // Find the fillable array
        if (preg_match('/(protected array \$fillable = \[)(.*?)(\];)/s', $content, $matches)) {
            $existingFields = $matches[2];

            // Add new fields
            foreach ($actions['add_to_fillable'] as $field) {
                // Check if field already exists
                if (strpos($existingFields, "'$field'") === false) {
                    $existingFields = trim($existingFields, "\n ") . ",\n        '$field'";
                    $modified = true;
                }
            }

            $newFillable = $matches[1] . "\n" . $existingFields . "\n    " . $matches[3];
            $content = str_replace($matches[0], $newFillable, $content);
            echo "   ✅ Champs ajoutés au \$fillable\n";
        }
    }

    // Create fillable array
    if (isset($actions['create_fillable'])) {
        // Find after "protected static string $table"
        if (preg_match('/(protected static string \$table = \'[^\']+\';)/', $content, $matches)) {
            $fillableCode = "\n\n    protected array \$fillable = [\n";
            foreach ($actions['create_fillable'] as $field) {
                $fillableCode .= "        '$field',\n";
            }
            $fillableCode = rtrim($fillableCode, ",\n") . "\n    ];";

            $content = str_replace($matches[1], $matches[1] . $fillableCode, $content);
            $modified = true;
            echo "   ✅ \$fillable créé\n";
        }
    }

    if ($modified) {
        file_put_contents($fullPath, $content);
        echo "   💾 Sauvegardé\n\n";
    } else {
        echo "   ℹ️  Aucune modification\n\n";
    }
}

echo str_repeat("=", 70) . "\n";
echo "✅ Terminé!\n";
