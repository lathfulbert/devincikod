# Correction : Erreur de Dépassement de Solde Wallet

## Problème rencontré

```
Failed to add credit: SQLSTATE[22003]: Numeric value out of range: 1264
Out of range value for column 'balance' at row 1
```

Cette erreur SQL se produit lorsqu'une tentative de recharge fait dépasser la limite du type de données `decimal(15,2)` utilisé pour la colonne `balance`.

## Analyse du problème

### Structure de la base de données

```sql
balance decimal(15,2) DEFAULT '0.00'
```

**Capacité maximale :** `9,999,999,999,999.99` (environ 10 trillions XOF)

### Causes possibles

1. **Montant de recharge excessif** : L'utilisateur entre un montant très élevé (ex: 999999999999999)
2. **Cumul dépassant la limite** : Solde actuel + montant de recharge > capacité max
3. **Absence de validation** : Aucune limite n'était définie côté serveur ou client

## Solution implémentée

### 1. Validations côté serveur (PHP)

Dans `Modules\Wallet\Controllers\WalletController@processTopup()` :

```php
// Conversion en float
$amount = (float)($_POST['amount'] ?? 0);

// Validation montant > 0
if ($amount <= 0) {
    flash('error', 'Le montant doit être supérieur à 0');
    redirect('/admin/wallet/topup');
    exit;
}

// Validation montant minimum
if ($amount < 100) {
    flash('error', 'Le montant minimum est de 100 XOF');
    redirect('/admin/wallet/topup');
    exit;
}

// Validation montant maximum par recharge
if ($amount > 10000000) {
    flash('error', 'Le montant maximum par recharge est de 10,000,000 XOF');
    redirect('/admin/wallet/topup');
    exit;
}

// Validation du solde résultant
$currentBalance = $this->walletService->getBalance($userId);
$newBalance = $currentBalance + $amount;

if ($newBalance > 9999999999999.99) {
    flash('error', 'Cette recharge dépasserait la limite maximale du solde');
    redirect('/admin/wallet/topup');
    exit;
}
```

### 2. Validations côté client (HTML5)

Dans `Modules\Wallet\Views\wallet\topup.php` :

```html
<input class="form-control" type="number" name="amount"
    min="100"
    max="10000000"
    step="1"
    required
    placeholder="Minimum 100 XOF">
```

**Attributs HTML5 :**
- `min="100"` : Empêche les montants < 100
- `max="10000000"` : Empêche les montants > 10,000,000
- `step="1"` : Force des valeurs entières (pas de centimes)

### 3. Validations JavaScript

```javascript
// Validation à la soumission
form.addEventListener('submit', function(e) {
    const amount = parseFloat(amountInput.value);

    // Vérification minimum
    if (isNaN(amount) || amount < 100) {
        e.preventDefault();
        alert('Le montant minimum est de 100 XOF');
        return false;
    }

    // Vérification maximum par recharge
    if (amount > 10000000) {
        e.preventDefault();
        alert('Le montant maximum par recharge est de 10,000,000 XOF');
        return false;
    }

    // Vérification du solde résultant
    const currentBalance = <?= $wallet->balance ?? 0 ?>;
    const newBalance = currentBalance + amount;

    if (newBalance > 9999999999999.99) {
        e.preventDefault();
        alert('Cette recharge dépasserait la limite maximale du solde');
        return false;
    }
});

// Validation en temps réel
amountInput.addEventListener('input', function() {
    const amount = parseFloat(this.value);

    if (amount > 10000000) {
        this.setCustomValidity('Le montant maximum est de 10,000,000 XOF');
    } else if (amount < 100 && amount > 0) {
        this.setCustomValidity('Le montant minimum est de 100 XOF');
    } else {
        this.setCustomValidity('');
    }
});
```

## Limites définies

| Limite | Valeur | Raison |
|--------|--------|--------|
| **Montant minimum** | 100 XOF | Éviter les micro-transactions |
| **Montant maximum par recharge** | 10,000,000 XOF | Sécurité et prévention fraude |
| **Solde maximum** | 9,999,999,999,999.99 XOF | Limite technique de `decimal(15,2)` |

## Protection multicouche

### Couche 1 : HTML5 (attributs min/max)
```html
<input min="100" max="10000000">
```
- Empêche la saisie de valeurs hors limites
- Support navigateur moderne

### Couche 2 : JavaScript temps réel
```javascript
input.addEventListener('input', ...)
```
- Validation instantanée
- Feedback visuel (`setCustomValidity`)

### Couche 3 : JavaScript soumission
```javascript
form.addEventListener('submit', ...)
```
- Dernière vérification avant envoi
- Vérification du solde résultant

### Couche 4 : PHP serveur (sécurité ultime)
```php
if ($amount > 10000000) { ... }
if ($newBalance > 9999999999999.99) { ... }
```
- Protection contre manipulations directes
- Validation définitive

## Messages d'erreur

### Erreurs serveur (flash messages)

| Condition | Message |
|-----------|---------|
| User ID manquant | `"ID utilisateur invalide"` |
| Montant ≤ 0 | `"Le montant doit être supérieur à 0"` |
| Montant < 100 | `"Le montant minimum est de 100 XOF"` |
| Montant > 10,000,000 | `"Le montant maximum par recharge est de 10,000,000 XOF"` |
| Solde résultant > max | `"Cette recharge dépasserait la limite maximale du solde"` |
| Exception | `"Échec de la recharge : [message]"` |

### Erreurs client (JavaScript alerts)

- `"Le montant minimum est de 100 XOF"`
- `"Le montant maximum par recharge est de 10,000,000 XOF"`
- `"Cette recharge dépasserait la limite maximale du solde (9,999,999,999,999.99 XOF)"`

## Message de succès amélioré

```php
$_SESSION['flash_success'] = "Recharge de " . number_format($amount, 0, ',', ' ') . " XOF effectuée avec succès";
```

**Exemple :**
- Avant : `"Successfully added 5000 XOF to your wallet"`
- Après : `"Recharge de 5 000 XOF effectuée avec succès"`

## Tests recommandés

### Test 1 : Montant valide
```
Input: 5000 XOF
Expected: ✓ Recharge réussie
```

### Test 2 : Montant < minimum
```
Input: 50 XOF
Expected: ✗ "Le montant minimum est de 100 XOF"
```

### Test 3 : Montant > maximum
```
Input: 50000000 XOF
Expected: ✗ "Le montant maximum par recharge est de 10,000,000 XOF"
```

### Test 4 : Solde résultant > limite
```
Current balance: 9,999,999,999,000 XOF
Input: 10000 XOF
New balance would be: 9,999,999,999,010 XOF (< 9,999,999,999,999.99)
Expected: ✓ Recharge réussie

Current balance: 9,999,990,000,000 XOF
Input: 10000000 XOF
New balance would be: 10,000,000,000,000 XOF (> 9,999,999,999,999.99)
Expected: ✗ "Cette recharge dépasserait la limite maximale du solde"
```

### Test 5 : Validation HTML5
```
1. Ouvrir le formulaire de recharge
2. Tenter de saisir 99
Expected: Navigateur empêche ou affiche erreur

3. Tenter de saisir 11000000
Expected: Navigateur empêche ou affiche erreur
```

### Test 6 : Contournement avec DevTools
```
1. Modifier les attributs min/max avec DevTools
2. Soumettre avec montant invalide
Expected: ✗ Bloqué par validation serveur PHP
```

## Scénarios d'erreur résolus

### Scénario 1 : L'erreur originale

**Avant :**
```
User enters: 999999999999999
Current balance: 229,429
New balance: 1,000,000,000,229,428 (> decimal(15,2) max)
Result: SQLSTATE[22003] Error
```

**Après :**
```
User tries to enter: 999999999999999
HTML5: Blocked at 10,000,000
JavaScript: Alert "Le montant maximum par recharge est de 10,000,000 XOF"
PHP: If bypassed, rejected with flash error
Result: ✓ Recharge bloquée, erreur claire
```

### Scénario 2 : Cumul excessif

**Avant :**
```
Current balance: 9,999,999,000,000
User adds: 10,000,000
New balance: 10,000,009,000,000 (> max)
Result: SQLSTATE[22003] Error
```

**Après :**
```
Current balance: 9,999,999,000,000
User tries to add: 10,000,000
JavaScript: Calculates newBalance = 10,000,009,000,000
Alert: "Cette recharge dépasserait la limite maximale du solde"
PHP: Double-check and reject
Result: ✓ Recharge bloquée avant erreur SQL
```

## Fichiers modifiés

### 1. `Modules\Wallet\Controllers\WalletController.php`

**Méthode `processTopup()` :**
- ✅ Conversion `$amount` en float (ligne 66)
- ✅ Validation user ID (ligne 71-75)
- ✅ Validation montant > 0 (ligne 77-81)
- ✅ Validation montant minimum 100 (ligne 83-87)
- ✅ Validation montant maximum 10M (ligne 91-95)
- ✅ Validation solde résultant (ligne 98-106)
- ✅ Message de succès formaté (ligne 109)

### 2. `Modules\Wallet\Views\wallet\topup.php`

**Champ de montant :**
- ✅ Attribut `max="10000000"` ajouté (ligne 41)
- ✅ Texte d'aide mis à jour (ligne 44-46)

**Scripts JavaScript :**
- ✅ Validation à la soumission (ligne 111-138)
- ✅ Validation en temps réel (ligne 141-151)

### 3. `WALLET_BALANCE_FIX.md`
- ✅ Documentation complète de la correction

## Améliorations futures possibles

1. **Limites personnalisables**
   - Permettre à l'admin de définir les limites min/max
   - Stockage dans une table de configuration

2. **Alertes de seuil**
   - Notifier l'utilisateur quand le solde approche la limite
   - Warning à 80%, 90%, 95% de la capacité

3. **Historique des tentatives rejetées**
   - Logger les tentatives de recharge bloquées
   - Analyse des patterns suspects

4. **Validation côté API**
   - Si des recharges sont possibles via API
   - Mêmes validations dans `ApiMiddleware`

5. **Type de données alternatif**
   - Migration vers `decimal(20,2)` si nécessaire
   - Ou utilisation de `bigint` (stocker en centimes)

## Compatibilité

- ✅ PHP 7.4+
- ✅ MySQL 5.7+
- ✅ Navigateurs modernes (HTML5 validation)
- ✅ JavaScript ES6+

## Support

En cas de problème :
1. Vérifier les flash messages pour le message d'erreur exact
2. Consulter les logs PHP pour les exceptions
3. Vérifier la console JavaScript pour les erreurs client
4. Tester avec des montants dans les limites définies
