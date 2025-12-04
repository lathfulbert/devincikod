<?php

/**
 * Script de test pour le système Author Tracking
 *
 * Usage: php Core/Database/test_author_tracking.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database\Database;
use Modules\SmsCore\Models\SmsMessage;
use Modules\SmsCore\Models\SmsCampaign;
use Modules\SmsCore\Models\SenderName;

echo "🧪 Test du système Author Tracking\n";
echo "=====================================\n\n";

// Simuler un utilisateur connecté
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'test_user',
    'email' => 'test@example.com'
];

echo "✅ Utilisateur de test configuré : ID = 1\n\n";

// Test 1 : Vérifier la connexion à la base de données
echo "📋 Test 1 : Connexion à la base de données\n";
try {
    $db = Database::getInstance();
    echo "   ✅ Connexion réussie\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2 : Vérifier que les colonnes existent
echo "📋 Test 2 : Vérification des colonnes dans sms_messages\n";
try {
    $pdo = $db->getPdo();
    $columns = ['created_by', 'updated_by'];

    foreach ($columns as $column) {
        $stmt = $pdo->query("SHOW COLUMNS FROM sms_messages LIKE '{$column}'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Colonne '{$column}' existe\n";
        } else {
            echo "   ⚠️  Colonne '{$column}' n'existe pas (exécutez la migration)\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur : " . $e->getMessage() . "\n\n";
}

// Test 3 : Créer un SMS et vérifier le tracking
echo "📋 Test 3 : Création d'un SMS de test\n";
try {
    $sms = new SmsMessage();
    $sms->to = '+221771234567';
    $sms->from = 'TEST';
    $sms->message = 'Test Author Tracking';
    $sms->gateway = 'test';
    $sms->status = 'pending';
    $sms->message_id = 'TEST-' . uniqid();
    $sms->save();

    echo "   ✅ SMS créé avec ID: {$sms->id}\n";

    if ($sms->created_by == 1) {
        echo "   ✅ created_by correctement rempli : {$sms->created_by}\n";
    } else {
        echo "   ❌ created_by non rempli (valeur: " . ($sms->created_by ?? 'NULL') . ")\n";
    }

    if ($sms->updated_by == 1) {
        echo "   ✅ updated_by correctement rempli : {$sms->updated_by}\n";
    } else {
        echo "   ❌ updated_by non rempli (valeur: " . ($sms->updated_by ?? 'NULL') . ")\n";
    }

    echo "\n";

    // Test 4 : Mettre à jour le SMS
    echo "📋 Test 4 : Mise à jour du SMS\n";
    $_SESSION['user']['id'] = 2; // Simuler un autre utilisateur

    $sms->status = 'sent';
    $sms->save();

    if ($sms->updated_by == 2) {
        echo "   ✅ updated_by mis à jour correctement : {$sms->updated_by}\n";
    } else {
        echo "   ❌ updated_by non mis à jour (valeur: " . ($sms->updated_by ?? 'NULL') . ")\n";
    }

    if ($sms->created_by == 1) {
        echo "   ✅ created_by reste inchangé : {$sms->created_by}\n";
    } else {
        echo "   ❌ created_by modifié par erreur : {$sms->created_by}\n";
    }

    echo "\n";

    // Test 5 : Méthodes helper
    echo "📋 Test 5 : Méthodes helper\n";

    $creatorName = $sms->getCreatorName();
    echo "   ✅ getCreatorName() : " . ($creatorName ?? 'NULL') . "\n";

    $updaterName = $sms->getUpdaterName();
    echo "   ✅ getUpdaterName() : " . ($updaterName ?? 'NULL') . "\n";

    if ($sms->isCreatedBy(1)) {
        echo "   ✅ isCreatedBy(1) : true\n";
    } else {
        echo "   ❌ isCreatedBy(1) : false\n";
    }

    if ($sms->isUpdatedBy(2)) {
        echo "   ✅ isUpdatedBy(2) : true\n";
    } else {
        echo "   ❌ isUpdatedBy(2) : false\n";
    }

    echo "\n";

    // Test 6 : Nettoyage
    echo "📋 Test 6 : Nettoyage\n";
    $db->query("DELETE FROM sms_messages WHERE id = ?", [$sms->id]);
    echo "   ✅ SMS de test supprimé\n\n";

} catch (Exception $e) {
    echo "   ❌ Erreur : " . $e->getMessage() . "\n";
    echo "   Trace : " . $e->getTraceAsString() . "\n\n";
}

// Test 7 : Test avec SenderName (Soft Delete)
echo "📋 Test 7 : Test Soft Delete avec SenderName\n";
try {
    $_SESSION['user']['id'] = 1;

    // Vérifier si la colonne deleted_by existe
    $stmt = $pdo->query("SHOW COLUMNS FROM sender_names LIKE 'deleted_by'");
    if ($stmt->rowCount() === 0) {
        echo "   ⚠️  Colonne 'deleted_by' n'existe pas (exécutez la migration)\n\n";
    } else {
        // Créer un sender name de test
        $senderName = new SenderName();
        $senderName->name = 'TEST-' . uniqid();
        $senderName->operator = 'TEST';
        $senderName->status = 'pending';
        $senderName->is_active = 0;
        $senderName->save();

        echo "   ✅ SenderName créé avec ID: {$senderName->id}\n";

        // Soft delete
        $_SESSION['user']['id'] = 3;
        $senderName->delete();

        // Vérifier deleted_by
        $stmt = $pdo->query("SELECT deleted_by, deleted_at FROM sender_names WHERE id = ?", [$senderName->id]);
        $result = $stmt->fetch();

        if ($result && $result['deleted_by'] == 3) {
            echo "   ✅ deleted_by correctement rempli : {$result['deleted_by']}\n";
        } else {
            echo "   ❌ deleted_by non rempli (valeur: " . ($result['deleted_by'] ?? 'NULL') . ")\n";
        }

        if ($result && $result['deleted_at']) {
            echo "   ✅ deleted_at correctement rempli : {$result['deleted_at']}\n";
        }

        // Nettoyage
        $senderName->forceDelete();
        echo "   ✅ SenderName de test supprimé\n\n";
    }

} catch (Exception $e) {
    echo "   ❌ Erreur : " . $e->getMessage() . "\n\n";
}

// Résumé
echo "=====================================\n";
echo "🎉 Tests terminés !\n\n";
echo "Si des colonnes n'existent pas, exécutez :\n";
echo "   php public/index.php migrate\n\n";
echo "Pour plus d'informations, consultez :\n";
echo "   Core/Database/AUTHOR_TRACKING.md\n";
echo "   Core/Database/QUICK_START_AUTHOR_TRACKING.md\n";
