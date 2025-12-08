# Améliorations de la Gestion du Solde SMS

## Résumé
Ce document décrit les améliorations apportées pour mieux gérer les erreurs de solde insuffisant lors de l'envoi d'OTP SMS.

## Problème Initial
- Les SMS OTP n'étaient pas envoyés en cas de solde insuffisant
- Le système affichait "Code de vérification envoyé" même en cas d'échec
- Aucun message d'erreur clair n'était affiché à l'utilisateur
- Les logs montraient "Failed to send SMS OTP: Insufficient balance" mais le système retournait `success: true`

## Solutions Implémentées

### 1. Détection des Erreurs de Solde Insuffisant
**Fichier**: `Modules/Auth/Providers/SmsOtpProvider.php`

**Changements**:
- Ajout de la détection des messages "Insufficient balance" dans les erreurs
- Lancement d'une exception avec un message clair en français
- Suppression du `return true` fallback qui masquait les erreurs

```php
// Avant
if ($result['success']) {
    $smsMessage->markAsSent($result['gateway_message_id'] ?? '');
} else {
    $smsMessage->markAsFailed($result['message'] ?? 'Unknown error');
    error_log("Failed to send SMS OTP: " . ($result['message'] ?? 'Unknown error'));
    error_log("SMS OTP for user {$userId}: {$code}");
    return true; // ❌ Masque l'erreur
}

// Après
if ($result['success']) {
    $smsMessage->markAsSent($result['gateway_message_id'] ?? '');
} else {
    $smsMessage->markAsFailed($result['message'] ?? 'Unknown error');
    error_log("Failed to send SMS OTP: " . ($result['message'] ?? 'Unknown error'));

    // Détection du solde insuffisant
    $errorMessage = $result['message'] ?? '';
    if (stripos($errorMessage, 'insufficient balance') !== false ||
        stripos($errorMessage, 'solde insuffisant') !== false ||
        stripos($errorMessage, 'balance insuffisante') !== false) {
        throw new \Exception("Solde SMS insuffisant. Veuillez recharger votre compte SMS.");
    }

    // Pour les autres erreurs
    error_log("SMS OTP for user {$userId}: {$code}");
    throw new \Exception("Échec d'envoi du SMS: " . $errorMessage);
}
```

### 2. Vérification Préventive du Solde
**Fichier**: `Modules/Auth/Providers/SmsOtpProvider.php`

**Changements**:
- Vérification du solde AVANT d'essayer d'envoyer le SMS
- Calcul du coût estimé basé sur le nombre de segments
- Message d'erreur incluant le coût estimé

```php
// Vérification du solde avant envoi
$message = "Votre code de vérification est: {$code}. Valide pour 2 minutes.";
$pricingData = $pricingService->calculatePrice($phone, $message);
$segments = $pricingService->countSegments($message);
$estimatedCost = $pricingData['unit_cost'] * $segments;

if (!$billingService->checkBalance($userId, $estimatedCost)) {
    throw new \Exception("Solde SMS insuffisant. Coût estimé: {$estimatedCost} {$pricingData['currency']}. Veuillez recharger votre compte.");
}
```

### 3. Affichage des Messages d'Erreur
**Fichier**: `Modules/Auth/Controllers/MfaController.php`

**Changements**:
- Capture des exceptions lors de l'envoi automatique d'OTP
- Affichage du message d'erreur complet dans `$_SESSION['flash_error']`
- Suppression des logs de debug inutiles

```php
// Méthode showChallenge()
try {
    $this->mfaManager->sendOtp($userId, $method['type']);
    $_SESSION['mfa_otp_sent'] = true;
    $_SESSION['flash_success'] = 'Code de vérification envoyé avec succès';
} catch (\Exception $e) {
    error_log("ERROR sending OTP: " . $e->getMessage());
    $_SESSION['flash_error'] = $e->getMessage(); // ✅ Affiche le message complet
}

// Méthode sendOtp() (AJAX)
catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi du code: ' . $e->getMessage()
    ]);
}
```

## Résultats

### Avant les Améliorations
- ❌ Message trompeur: "Code de vérification envoyé" alors qu'aucun SMS n'est envoyé
- ❌ Pas d'indication à l'utilisateur du problème de solde
- ❌ Nécessite de vérifier les logs PHP pour comprendre le problème

### Après les Améliorations
- ✅ Message clair: "Solde SMS insuffisant. Coût estimé: 35 XOF. Veuillez recharger votre compte."
- ✅ Vérification préventive du solde avant tentative d'envoi
- ✅ Erreur affichée immédiatement sur la page MFA challenge
- ✅ Erreur affichée dans la popup AJAX lors du clic sur "Renvoyer un nouveau code"

## Messages d'Erreur Possibles

### Solde Insuffisant
```
Solde SMS insuffisant. Coût estimé: 35 XOF. Veuillez recharger votre compte.
```

### Autres Erreurs SMS
```
Échec d'envoi du SMS: [message d'erreur du gateway]
```

### Session Invalide
```
Non autorisé. Session MFA invalide.
```

## Tests Recommandés

1. **Test avec solde insuffisant**:
   - Vider le solde SMS de l'utilisateur
   - Tenter de se connecter
   - Vérifier que le message "Solde SMS insuffisant" s'affiche
   - Vérifier que l'utilisateur ne peut pas continuer sans recharger

2. **Test avec solde suffisant**:
   - Recharger le compte SMS
   - Tenter de se connecter
   - Vérifier que le SMS est bien envoyé
   - Vérifier le message "Code de vérification envoyé avec succès"

3. **Test du bouton "Renvoyer"**:
   - Cliquer sur "Renvoyer un nouveau code" avec solde insuffisant
   - Vérifier que l'erreur s'affiche dans la popup
   - Recharger et vérifier que ça fonctionne

## Fichiers Modifiés

1. `Modules/Auth/Providers/SmsOtpProvider.php`
   - Ajout de la détection d'erreurs de solde insuffisant
   - Ajout de la vérification préventive du solde
   - Suppression du fallback `return true` qui masquait les erreurs

2. `Modules/Auth/Controllers/MfaController.php`
   - Amélioration de la gestion des erreurs dans `showChallenge()`
   - Affichage du message d'exception complet
   - Nettoyage des logs de debug

## Notes Importantes

- Le coût estimé est calculé en fonction du nombre de segments du message
- Un message OTP standard fait 1 segment (< 160 caractères)
- Le message actuel: "Votre code de vérification est: 123456. Valide pour 2 minutes." fait 65 caractères
- Coût typique: 35 XOF par SMS

## Maintenance Future

1. Envisager d'ajouter un seuil d'avertissement (ex: alerter l'utilisateur si solde < 500 XOF)
2. Ajouter une page d'administration pour voir le solde SMS en temps réel
3. Envoyer des notifications email quand le solde est bas
4. Permettre la recharge automatique du solde SMS
