<?php
/**
 * Script de test rapide pour attribuer des Sender Names
 *
 * Usage: php Modules/SmsCore/test_assign_sender_names.php
 */

require_once __DIR__ . '/../../bootstrap.php';

use Modules\SmsCore\Models\SenderName;

echo "=== Test d'Attribution des Sender Names ===\n\n";

// ID de l'utilisateur admin
$userId = 1;
$assignedBy = 1;

echo "1. Récupération des Sender Names actifs et approuvés...\n";
$senderNames = SenderName::getActiveApproved();
echo "   Trouvé: " . count($senderNames) . " Sender Name(s)\n\n";

if (empty($senderNames)) {
    echo "❌ Aucun Sender Name trouvé!\n";
    exit(1);
}

// Afficher les Sender Names disponibles
echo "2. Sender Names disponibles:\n";
foreach ($senderNames as $sn) {
    echo "   - ID {$sn->id}: {$sn->name} ({$sn->operator})\n";
}
echo "\n";

// Attribuer tous les Sender Names à l'utilisateur
echo "3. Attribution à l'utilisateur ID $userId...\n";
$senderNameIds = array_column($senderNames, 'id');
$success = SenderName::syncForUser($userId, $senderNameIds, $assignedBy);

if ($success) {
    echo "   ✅ Attribution réussie!\n\n";
} else {
    echo "   ❌ Échec de l'attribution!\n\n";
    exit(1);
}

// Vérifier les attributions
echo "4. Vérification des attributions...\n";
$userSenderNames = SenderName::getForUser($userId);
echo "   L'utilisateur a maintenant accès à " . count($userSenderNames) . " Sender Name(s):\n";

foreach ($userSenderNames as $sn) {
    $hasAccess = SenderName::userHasAccess($userId, $sn->id);
    $status = $hasAccess ? '✅' : '❌';
    echo "   $status {$sn->name} (ID: {$sn->id})\n";
}

echo "\n=== Test terminé avec succès! ===\n";
echo "\nVous pouvez maintenant:\n";
echo "1. Accéder à /sms/sender-names pour gérer les Sender Names\n";
echo "2. Utiliser l'API: GET /api/sms/sender-names/user\n";
echo "3. Intégrer dans vos formulaires d'envoi SMS\n\n";

// Test API
echo "Test API:\n";
echo "curl -X GET http://localhost/api/sms/sender-names/user \\\n";
echo "  -H 'Cookie: PHPSESSID=your_session_id'\n\n";

// SQL pour vérifier
echo "SQL de vérification:\n";
echo "SELECT sn.*, usn.assigned_at FROM sender_names sn\n";
echo "JOIN user_sender_names usn ON sn.id = usn.sender_name_id\n";
echo "WHERE usn.user_id = $userId;\n";
