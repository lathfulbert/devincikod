<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

$db = \App\Core\Database\Database::getInstance()->getPdo();

echo "🔍 Vérification complète: Modèles vs Tables\n";
echo str_repeat("=", 70) . "\n\n";

// Liste de tous les modèles avec HasAuthor
$models = [
    // SmsCore
    ['model' => 'Modules\SmsCore\Models\SmsMessage', 'table' => 'sms_messages'],
    ['model' => 'Modules\SmsCore\Models\SmsCampaign', 'table' => 'sms_campaigns'],
    ['model' => 'Modules\SmsCore\Models\SenderName', 'table' => 'sender_names'],
    ['model' => 'Modules\SmsCore\Models\SmsQueue', 'table' => 'sms_queue'],
    ['model' => 'Modules\SmsCore\Models\SmsBillingLog', 'table' => 'sms_billing_logs'],

    // Wallet
    ['model' => 'Modules\Wallet\Models\Wallet', 'table' => 'wallets'],
    ['model' => 'Modules\Wallet\Models\WalletTransaction', 'table' => 'wallet_transactions'],

    // Settings
    ['model' => 'Modules\Settings\Models\Setting', 'table' => 'settings'],
    ['model' => 'Modules\Settings\Models\SmsGateway', 'table' => 'sms_gateways'],
    ['model' => 'Modules\Settings\Models\WalletGateway', 'table' => 'wallet_gateways'],

    // RBAC
    ['model' => 'Modules\RBAC\Models\Role', 'table' => 'roles'],
    ['model' => 'Modules\RBAC\Models\Permission', 'table' => 'permissions'],

    // EmailMarketing
    ['model' => 'Modules\EmailMarketing\Models\EmailCampaign', 'table' => 'email_campaigns'],
    ['model' => 'Modules\EmailMarketing\Models\EmailTemplate', 'table' => 'email_templates'],
    ['model' => 'Modules\EmailMarketing\Models\Workflow', 'table' => 'workflows'],

    // Contacts
    ['model' => 'Modules\Contacts\Models\Contact', 'table' => 'contacts'],

    // ApiKeys
    ['model' => 'Modules\ApiKeys\Models\ApiKey', 'table' => 'api_keys'],
];

$issues = [];
$success = 0;

foreach ($models as $info) {
    $modelClass = $info['model'];
    $table = $info['table'];
    $modelName = basename(str_replace('\\', '/', $modelClass));

    echo "📋 $modelName ($table)\n";

    // Check if model class exists
    if (!class_exists($modelClass)) {
        echo "   ⚠️  Classe non trouvée\n\n";
        continue;
    }

    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        echo "   ⚠️  Table non trouvée\n\n";
        continue;
    }

    // Get model traits
    $traits = class_uses($modelClass);
    $hasAuthorTrait = in_array('App\Core\Database\Traits\HasAuthor', $traits);
    $hasSoftDeletesTrait = in_array('App\Core\Database\Traits\SoftDeletes', $traits);

    echo "   Trait HasAuthor: " . ($hasAuthorTrait ? '✅' : '❌') . "\n";
    echo "   Trait SoftDeletes: " . ($hasSoftDeletesTrait ? '✅' : '❌') . "\n";

    // Get model fillable
    $reflection = new ReflectionClass($modelClass);
    $fillableProperty = $reflection->getProperty('fillable');
    $fillableProperty->setAccessible(true);
    $fillable = $fillableProperty->getValue(new $modelClass());

    $hasCreatedByFillable = in_array('created_by', $fillable);
    $hasUpdatedByFillable = in_array('updated_by', $fillable);
    $hasDeletedByFillable = in_array('deleted_by', $fillable);

    // Get table columns
    $stmt = $db->query("SHOW COLUMNS FROM `$table`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $hasCreatedByColumn = in_array('created_by', $columns);
    $hasUpdatedByColumn = in_array('updated_by', $columns);
    $hasDeletedAtColumn = in_array('deleted_at', $columns);
    $hasDeletedByColumn = in_array('deleted_by', $columns);

    // Check for issues
    $modelIssues = [];

    // If HasAuthor trait is used
    if ($hasAuthorTrait) {
        // created_by should exist in both table and fillable
        if (!$hasCreatedByColumn) {
            $modelIssues[] = "❌ Colonne 'created_by' manquante dans la table";
        }
        if (!$hasCreatedByFillable) {
            $modelIssues[] = "⚠️  'created_by' manquant dans \$fillable";
        }

        // updated_by should exist in both table and fillable
        if (!$hasUpdatedByColumn) {
            $modelIssues[] = "❌ Colonne 'updated_by' manquante dans la table";
        }
        if (!$hasUpdatedByFillable) {
            $modelIssues[] = "⚠️  'updated_by' manquant dans \$fillable";
        }
    }

    // If SoftDeletes trait is used
    if ($hasSoftDeletesTrait) {
        // deleted_at should exist in table
        if (!$hasDeletedAtColumn) {
            $modelIssues[] = "❌ Colonne 'deleted_at' manquante dans la table";
        }

        // deleted_by should exist in both table and fillable (if HasAuthor is also used)
        if ($hasAuthorTrait) {
            if (!$hasDeletedByColumn) {
                $modelIssues[] = "❌ Colonne 'deleted_by' manquante dans la table";
            }
            if (!$hasDeletedByFillable) {
                $modelIssues[] = "⚠️  'deleted_by' manquant dans \$fillable";
            }
        }
    }

    if (empty($modelIssues)) {
        echo "   ✅ Tout est OK!\n";
        $success++;
    } else {
        echo "   ⚠️  PROBLÈMES DÉTECTÉS:\n";
        foreach ($modelIssues as $issue) {
            echo "      $issue\n";
        }
        $issues[$modelName] = $modelIssues;
    }

    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "📊 RÉSUMÉ\n";
echo str_repeat("=", 70) . "\n\n";

echo "✅ Modèles OK: $success / " . count($models) . "\n";
echo "⚠️  Modèles avec problèmes: " . count($issues) . "\n\n";

if (!empty($issues)) {
    echo "🔧 ACTIONS REQUISES:\n";
    echo str_repeat("-", 70) . "\n\n";

    foreach ($issues as $modelName => $modelIssues) {
        echo "📌 $modelName:\n";
        foreach ($modelIssues as $issue) {
            echo "   $issue\n";
        }
        echo "\n";
    }

    echo "\n💡 Pour corriger automatiquement, utilisez:\n";
    echo "   php fix_missing_author_columns.php\n\n";
} else {
    echo "🎉 TOUT EST PARFAIT!\n";
    echo "Tous les modèles sont correctement configurés.\n\n";
}
