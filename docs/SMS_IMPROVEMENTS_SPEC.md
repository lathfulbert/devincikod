# 📱 Spécifications des Améliorations SMS

**Date**: 4 Décembre 2025

---

## 🎯 Vue d'ensemble

Trois améliorations majeures pour le système d'envoi de SMS:

1. **Préfixe téléphonique automatique par pays**
2. **Send SMS avec support multiple numéros**
3. **Gestion automatique de la queue selon le volume**

---

## 1️⃣ Préfixe Téléphonique Automatique

### 📋 Description

Ajouter un setting pour le code pays par défaut qui sera automatiquement ajouté aux numéros de téléphone saisis.

### 🎯 Fonctionnalités

#### Settings SMS
- ✅ Champ "Code Pays Par Défaut" (ex: +225 pour Côte d'Ivoire)
- ✅ Option "Ajouter automatiquement le préfixe"
- ✅ Liste des codes pays prédéfinis

#### Comportement
- Si numéro commence par `+` → Ne rien ajouter
- Si numéro commence par `00` → Ne rien ajouter
- Si numéro commence par le code pays sans `+` (ex: 225) → Ajouter `+` uniquement
- Sinon → Ajouter le préfixe complet (ex: `07123456` → `+22507123456`)

### 📊 Tables impactées

**Table: `settings`**
```sql
-- Nouveaux settings à ajouter
INSERT INTO settings (key, value, type, setting_group, description) VALUES
('sms_default_country_code', '+225', 'string', 'sms', 'Code pays par défaut pour les numéros SMS'),
('sms_auto_add_prefix', '1', 'boolean', 'sms', 'Ajouter automatiquement le préfixe pays');
```

### 🔧 Fichiers à modifier

1. **Modules/Settings/Views/settings/sms.php**
   - Ajouter champs dans le formulaire

2. **Modules/SmsCore/Services/SmsService.php** (ou créer)
   - Fonction `formatPhoneNumber($number): string`

3. **Modules/SmsCore/Controllers/SmsController.php**
   - Appeler `formatPhoneNumber()` avant envoi

---

## 2️⃣ Send SMS - Support Multiple Numéros

### 📋 Description

Permettre l'envoi de SMS à plusieurs destinataires simultanément via:
- Saisie manuelle de plusieurs numéros
- Import d'un fichier CSV/Excel
- Sélection depuis les contacts

### 🎯 Fonctionnalités

#### Interface Utilisateur

**Onglets dans le formulaire Send SMS:**

1. **📝 Saisie Manuelle**
   - Textarea pour saisir plusieurs numéros (un par ligne ou séparés par virgule)
   - Validation en temps réel
   - Compteur de numéros valides

2. **📄 Import Fichier**
   - Upload CSV/Excel
   - Format attendu: une colonne avec numéros
   - Prévisualisation avant import
   - Détection automatique du format

3. **👥 Depuis Contacts**
   - Liste des contacts avec checkbox
   - Recherche et filtrage
   - Sélection multiple
   - Option "Tous les contacts"

#### Validation
- ✅ Vérifier format numéro
- ✅ Supprimer doublons
- ✅ Appliquer préfixe pays automatique
- ✅ Limiter nombre maximum (ex: 1000 par envoi)

### 📊 Tables impactées

**Aucune nouvelle table nécessaire** - Utiliser:
- `contacts` (existante)
- `sms_queue` (pour envois en masse)

### 🔧 Fichiers à créer/modifier

1. **Modules/SmsCore/Views/sms/send.php**
   - Ajouter onglets (Manual, File, Contacts)
   - Interface pour chaque mode

2. **Modules/SmsCore/Controllers/SmsController.php**
   - Méthode `sendBulk()` pour envois multiples
   - Méthode `parsePhoneNumbers()` pour traiter la saisie
   - Méthode `importFromFile()` pour fichiers
   - Méthode `getFromContacts()` pour contacts

3. **Modules/SmsCore/Services/FileImportService.php** (CRÉER)
   - Parser CSV
   - Parser Excel
   - Valider formats

### 📝 Format Fichier CSV

```csv
phone
+22507123456
+22507234567
07345678
```

Ou avec autres colonnes (ignorées):
```csv
name,phone,email
John,+22507123456,john@email.com
Jane,+22507234567,jane@email.com
```

---

## 3️⃣ Gestion Automatique de la Queue

### 📋 Description

Gérer automatiquement l'envoi via queue selon le volume de SMS:
- < 100 SMS → Envoi direct
- ≥ 100 SMS → Mise en queue d'attente

### 🎯 Fonctionnalités

#### Settings
- ✅ Seuil pour queue (défaut: 100)
- ✅ Mode forcé: "Toujours via queue" ou "Toujours direct"
- ✅ Délai entre envois en queue (pour éviter rate limiting)

#### Comportement

**Envoi Direct (< seuil)**
1. Envoyer immédiatement
2. Créer enregistrement dans `sms_messages`
3. Débiter wallet
4. Retourner résultat

**Envoi via Queue (≥ seuil)**
1. Créer enregistrements dans `sms_queue`
2. Message de confirmation "X SMS ajoutés à la queue"
3. Traitement asynchrone via cron
4. Notifications de progression

### 📊 Tables impactées

**Table: `settings`**
```sql
INSERT INTO settings (key, value, type, setting_group, description) VALUES
('sms_queue_threshold', '100', 'integer', 'sms', 'Nombre de SMS pour déclencher la queue'),
('sms_queue_mode', 'auto', 'select', 'sms', 'Mode queue: auto, always, never'),
('sms_queue_delay', '1', 'integer', 'sms', 'Délai en secondes entre chaque SMS en queue');
```

**Table: `sms_queue`** (déjà existante)
- Utiliser pour stocker les SMS en attente

### 🔧 Fichiers à créer/modifier

1. **Modules/Settings/Views/settings/sms.php**
   - Ajouter champs pour configuration queue

2. **Modules/SmsCore/Services/SmsQueueService.php** (CRÉER)
   - Méthode `shouldUseQueue(int $count): bool`
   - Méthode `addToQueue(array $recipients, string $message): int`
   - Méthode `processQueue(int $batchSize = 10): void`

3. **Modules/SmsCore/Controllers/SmsController.php**
   - Vérifier seuil avant envoi
   - Router vers queue ou envoi direct

4. **Modules/SmsCore/Cron/ProcessSmsQueue.php** (CRÉER)
   - Tâche cron pour traiter la queue
   - Exécution toutes les minutes

---

## 📅 Plan d'implémentation

### Phase 1: Préfixe Téléphonique (2-3h)
- [ ] Ajouter settings en base
- [ ] Créer service formatPhoneNumber
- [ ] Modifier formulaire settings
- [ ] Intégrer dans SmsController
- [ ] Tests

### Phase 2: Multiple Numéros (4-6h)
- [ ] Créer interface avec onglets
- [ ] Implémenter saisie manuelle
- [ ] Créer FileImportService
- [ ] Implémenter import fichier
- [ ] Intégrer sélection contacts
- [ ] Validation et déduplication
- [ ] Tests

### Phase 3: Gestion Queue (3-4h)
- [ ] Ajouter settings queue
- [ ] Créer SmsQueueService
- [ ] Modifier logique envoi
- [ ] Créer tâche cron
- [ ] Interface progression
- [ ] Tests

### Phase 4: Tests et Documentation (1-2h)
- [ ] Tests complets
- [ ] Documentation utilisateur
- [ ] Guide administrateur

**Durée totale estimée: 10-15h**

---

## 🎨 Maquettes UI

### Send SMS - Nouvelle Interface

```
┌─────────────────────────────────────────────────────┐
│  Envoyer un SMS                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Destinataires:                                     │
│  ┌─────┬──────────┬──────────┐                    │
│  │ 📝  │   📄     │   👥     │                    │
│  │Saisie│ Fichier │ Contacts │                    │
│  └─────┴──────────┴──────────┘                    │
│                                                     │
│  [Onglet Saisie Manuelle actif]                   │
│                                                     │
│  Numéros de téléphone:                             │
│  ┌─────────────────────────────────────────────┐  │
│  │ +22507123456                                │  │
│  │ 07234567                                    │  │
│  │ +22508345678                                │  │
│  │                                             │  │
│  └─────────────────────────────────────────────┘  │
│  ℹ️ Un numéro par ligne ou séparés par virgule    │
│  ✅ 3 numéros valides détectés                     │
│                                                     │
│  Message:                                           │
│  ┌─────────────────────────────────────────────┐  │
│  │ Votre message ici...                        │  │
│  │                                             │  │
│  └─────────────────────────────────────────────┘  │
│  160 caractères | 1 SMS                            │
│                                                     │
│  Sender Name: [Orange SMS ▼]                       │
│  Gateway: [Auto ▼]                                 │
│                                                     │
│  💰 Coût estimé: 3 SMS × 10 XOF = 30 XOF          │
│                                                     │
│  [Annuler]  [📤 Envoyer]                          │
└─────────────────────────────────────────────────────┘
```

### Settings SMS

```
┌─────────────────────────────────────────────────────┐
│  Paramètres SMS                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  🌍 Configuration Pays                              │
│  ├─ Code pays par défaut: [+225 ▼]                │
│  ├─ □ Ajouter automatiquement le préfixe          │
│  └─ Pays supportés: CI, SN, ML, BF, TG...         │
│                                                     │
│  📊 Gestion de la Queue                             │
│  ├─ Seuil pour queue: [100] SMS                   │
│  ├─ Mode: [Auto ▼] (Auto/Toujours/Jamais)        │
│  └─ Délai entre envois: [1] secondes              │
│                                                     │
│  📤 Import/Export                                   │
│  ├─ Formats acceptés: CSV, Excel                   │
│  └─ Taille max fichier: 5 MB                      │
│                                                     │
│  [💾 Enregistrer]                                  │
└─────────────────────────────────────────────────────┘
```

---

## ✅ Critères de validation

### Fonctionnels
- [ ] Préfixe s'ajoute automatiquement selon paramétrage
- [ ] Multiple numéros acceptés par tous les modes
- [ ] Import fichier fonctionne (CSV et Excel)
- [ ] Sélection contacts fonctionne
- [ ] Queue se déclenche automatiquement selon seuil
- [ ] Progression visible pour envois en queue

### Techniques
- [ ] Code compatible avec système Author Tracking
- [ ] Validation côté serveur ET client
- [ ] Gestion des erreurs robuste
- [ ] Performance optimisée (pas de timeout)
- [ ] Logs détaillés pour debugging

### UX
- [ ] Interface intuitive
- [ ] Messages clairs pour l'utilisateur
- [ ] Retour visuel sur chaque action
- [ ] Pas de perte de données en cas d'erreur

---

## 📚 Documentation à créer

1. **Guide Utilisateur**
   - Comment envoyer à plusieurs numéros
   - Comment importer un fichier
   - Comment utiliser les contacts

2. **Guide Administrateur**
   - Configuration des settings
   - Gestion de la queue
   - Monitoring des envois

3. **Documentation Technique**
   - API des nouveaux services
   - Structure des fichiers
   - Processus de queue

---

**Prêt à commencer l'implémentation?** 🚀
