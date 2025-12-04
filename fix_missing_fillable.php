<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "🔧 Correction automatique des modèles\n";
echo str_repeat("=", 70) . "\n\n";

$fixes = [
    // SmsMessage - Ajouter created_by et updated_by
    [
        'file' => 'Modules/SmsCore/Models/SmsMessage.php',
        'search' => "    protected array \$fillable = [\n        'user_id',\n        'to',\n        'from',\n        'message',\n        'gateway',\n        'status',\n        'message_id',\n        'gateway_message_id',\n        'cost',\n        'metadata',\n        'gateway_response',\n        'scheduled_at',\n        'sent_at',\n        'delivered_at',\n        'error'\n    ];",
        'replace' => "    protected array \$fillable = [\n        'user_id',\n        'to',\n        'from',\n        'message',\n        'gateway',\n        'status',\n        'message_id',\n        'gateway_message_id',\n        'cost',\n        'metadata',\n        'gateway_response',\n        'scheduled_at',\n        'sent_at',\n        'delivered_at',\n        'error',\n        'created_by',\n        'updated_by'\n    ];",
    ],

    // SmsCampaign - Ajouter updated_by
    [
        'file' => 'Modules/SmsCore/Models/SmsCampaign.php',
        'check_has' => 'created_by',
        'add_after' => "'created_by'",
        'add_line' => "'updated_by'",
    ],

    // SenderName - Ajouter updated_by et deleted_by
    [
        'file' => 'Modules/SmsCore/Models/SenderName.php',
        'check_has' => 'created_by',
        'add_after' => "'created_by'",
        'add_lines' => ["'updated_by'", "'deleted_by'"],
    ],

    // Setting - Ajouter created_by et updated_by au fillable
    [
        'file' => 'Modules/Settings/Models/Setting.php',
        'add_fillable' => ['created_by', 'updated_by'],
    ],

    // WalletGateway - Ajouter created_by et updated_by au fillable
    [
        'file' => 'Modules/Settings/Models/WalletGateway.php',
        'add_fillable' => ['created_by', 'updated_by'],
    ],

    // Role - Créer le $fillable
    [
        'file' => 'Modules/RBAC/Models/Role.php',
        'create_fillable' => ['name', 'description', 'created_by', 'updated_by', 'deleted_by'],
    ],
];

foreach ($fixes as $fix) {
    $file = __DIR__ . '/' . $fix['file'];

    if (!file_exists($file)) {
        echo "⚠️  Fichier non trouvé: {$fix['file']}\n";
        continue;
    }

    echo "📝 Traitement: {$fix['file']}\n";

    $content = file_get_contents($file);
    $modified = false;

    // Type 1: Simple search and replace
    if (isset($fix['search']) && isset($fix['replace'])) {
        if (strpos($content, $fix['search']) !== false) {
            $content = str_replace($fix['search'], $fix['replace'], $content);
            $modified = true;
            echo "   ✅ Modifié\n";
        } else {
            echo "   ⚠️  Pattern non trouvé\n";
        }
    }

    // Type 2: Create fillable array
    if (isset($fix['create_fillable'])) {
        // Find where to insert (after "protected static string $table")
        $pattern = '/(protected static string \$table = \'[^\']+\';)/';
        if (preg_match($pattern, $content, $matches)) {
            $fillableCode = "\n\n    protected array \$fillable = [\n";
            foreach ($fix['create_fillable'] as $field) {
                $fillableCode .= "        '$field',\n";
            }
            $fillableCode = rtrim($fillableCode, ",\n") . "\n    ];";

            $content = str_replace($matches[1], $matches[1] . $fillableCode, $content);
            $modified = true;
            echo "   ✅ \$fillable créé\n";
        }
    }

    if ($modified) {
        file_put_contents($file, $content);
        echo "   💾 Fichier sauvegardé\n\n";
    } else {
        echo "   ℹ️  Aucune modification nécessaire\n\n";
    }
}

echo str_repeat("=", 70) . "\n";
echo "✅ Correction terminée!\n\n";
echo "🧪 Exécutez maintenant:\n";
echo "   php verify_all_models_author_tracking.php\n\n";
