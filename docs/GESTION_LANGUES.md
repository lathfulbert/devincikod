# Gestion des Langues - Guide Complet

## Vue d'ensemble

Le système de gestion des langues vous permet d'ajouter, activer, désactiver et configurer les langues disponibles dans votre application SunuFramework2 directement depuis l'interface d'administration.

## 🎯 Fonctionnalités

- ✅ **Interface d'administration** pour gérer les langues
- ✅ **Activation/Désactivation** des langues
- ✅ **Configuration de la langue par défaut**
- ✅ **Configuration de la langue de fallback**
- ✅ **Création automatique** des fichiers de traduction
- ✅ **20+ langues préconfigurées** (FR, EN, AR, ES, DE, PT, IT, RU, ZH, JA, etc.)
- ✅ **Menu langue dynamique** dans le header
- ✅ **Support RTL** pour l'arabe et autres langues RTL

## 📍 Accès à l'interface

### URL d'accès
```
http://votre-domaine/admin/settings/languages
```

### Navigation dans le menu
```
Dashboard → Configuration Générale → Langues
```

## 🌍 Langues disponibles

Le système inclut 20+ langues préconfigurées dans [config/languages.php](config/languages.php) :

| Code | Langue | Nom natif | Drapeau | Direction |
|------|--------|-----------|---------|-----------|
| fr   | Français | Français | 🇫🇷 | LTR |
| en   | English | English | 🇺🇸 | LTR |
| ar   | Arabic | العربية | 🇦🇪 | RTL |
| es   | Spanish | Español | 🇪🇸 | LTR |
| de   | German | Deutsch | 🇩🇪 | LTR |
| pt   | Portuguese | Português | 🇵🇹 | LTR |
| it   | Italian | Italiano | 🇮🇹 | LTR |
| ru   | Russian | Русский | 🇷🇺 | LTR |
| zh   | Chinese | 中文 | 🇨🇳 | LTR |
| ja   | Japanese | 日本語 | 🇯🇵 | LTR |
| ko   | Korean | 한국어 | 🇰🇷 | LTR |
| hi   | Hindi | हिन्दी | 🇮🇳 | LTR |
| nl   | Dutch | Nederlands | 🇳🇱 | LTR |
| pl   | Polish | Polski | 🇵🇱 | LTR |
| tr   | Turkish | Türkçe | 🇹🇷 | LTR |
| sv   | Swedish | Svenska | 🇸🇪 | LTR |
| no   | Norwegian | Norsk | 🇳🇴 | LTR |
| da   | Danish | Dansk | 🇩🇰 | LTR |
| fi   | Finnish | Suomi | 🇫🇮 | LTR |
| el   | Greek | Ελληνικά | 🇬🇷 | LTR |

## 📖 Guide d'utilisation

### 1. Activer une nouvelle langue

1. Accédez à **Admin → Configuration Générale → Langues**
2. Trouvez la langue que vous souhaitez activer dans la liste
3. Cliquez sur le bouton **"Activer"** (bouton vert avec icône)
4. La langue apparaîtra immédiatement dans le menu langue du header
5. Si le fichier de traduction n'existe pas, créez-le avec le bouton **"Créer le fichier"**

**Exemple :** Pour activer l'espagnol (ES)
- Langue : Spanish (Español)
- Action : Cliquez sur "Activer"
- Résultat : ES apparaît dans le menu langue

### 2. Définir la langue par défaut

La langue par défaut est utilisée pour :
- Les nouveaux utilisateurs non authentifiés
- Les utilisateurs n'ayant pas défini de préférence
- Le contenu public de l'application

**Étapes :**
1. Dans la liste des langues, trouvez la langue souhaitée
2. Assurez-vous qu'elle est **active**
3. Cliquez sur le bouton **"Définir par défaut"** (étoile)
4. Confirmez l'action
5. La langue affiche maintenant le badge "Par défaut"

**Note :** Seule une langue active peut être définie par défaut.

### 3. Définir la langue de fallback

La langue de fallback est utilisée quand :
- Une traduction n'existe pas dans la langue active
- Un fichier de traduction est incomplet
- Une clé de traduction est manquante

**Étapes :**
1. Trouvez la langue dans la liste
2. Assurez-vous qu'elle est **active**
3. Cliquez sur le bouton **"Définir comme fallback"** (bouclier)
4. La langue affiche le badge "Fallback"

**Recommandation :** Utilisez l'anglais (EN) comme fallback car c'est la langue la plus complète.

### 4. Créer un fichier de traduction

Quand vous activez une nouvelle langue sans fichier de traduction :

1. Un indicateur **"Manquant"** apparaît dans la colonne "Fichier"
2. Cliquez sur le bouton **"Créer le fichier"** (icône +)
3. Un fichier JSON sera créé dans `/languages/{code}.json`
4. Le fichier contient un modèle de base avec les clés principales

**Contenu du modèle par défaut :**
```json
{
  "app": {
    "name": "SunuFramework",
    "tagline": "Modern modular PHP framework"
  },
  "auth": {
    "login": "Login",
    "logout": "Logout",
    "register": "Register",
    "welcome": "Welcome, :name"
  },
  "dashboard": {
    "title": "Dashboard",
    "welcome": "Welcome to your dashboard"
  },
  ...
}
```

### 5. Désactiver une langue

**Restrictions :**
- ❌ Impossible de désactiver la langue par défaut
- ❌ Impossible de désactiver la langue de fallback
- ✅ Possible pour toutes les autres langues actives

**Étapes :**
1. Trouvez la langue dans la liste
2. Cliquez sur le bouton **"Désactiver"** (bouton orange)
3. Confirmez l'action
4. La langue disparaît du menu langue du header

### 6. Gérer les traductions

Pour chaque langue, vous pouvez gérer les traductions :

1. Cliquez sur le bouton **"Gérer les traductions"** (icône crayon)
2. Vous êtes redirigé vers l'interface de traductions filtrée pour cette langue
3. Ajoutez, modifiez ou supprimez des traductions
4. Les modifications sont sauvegardées dans `/languages/{code}.json`

**URL directe :**
```
/admin/settings/translations?language={code}
```

## 🔧 Configuration technique

### Fichiers de configuration

#### [config/app.php](config/app.php)
```php
return [
    'locale' => 'fr',                          // Langue par défaut
    'fallback_locale' => 'en',                 // Langue de fallback
    'supported_locales' => ['fr', 'en', 'ar'], // Langues actives
];
```

#### [config/languages.php](config/languages.php)
```php
return [
    'fr' => [
        'name' => 'Français',
        'native_name' => 'Français',
        'flag' => 'flag-icon-fr',
        'short' => 'FR',
        'direction' => 'ltr'
    ],
    // ... autres langues
];
```

### Structure des fichiers de traduction

```
/languages/
  ├── fr.json          # Français
  ├── en.json          # English
  ├── ar.json          # العربية
  ├── es.json          # Español (après activation)
  └── de.json          # Deutsch (après activation)
```

### Routes disponibles

| Méthode | URL | Action |
|---------|-----|--------|
| GET | `/admin/settings/languages` | Liste des langues |
| POST | `/admin/settings/languages/activate` | Activer une langue |
| POST | `/admin/settings/languages/deactivate` | Désactiver une langue |
| POST | `/admin/settings/languages/set-default` | Définir par défaut |
| POST | `/admin/settings/languages/set-fallback` | Définir fallback |
| POST | `/admin/settings/languages/create-file` | Créer fichier JSON |

## 🎨 Composants

### Menu langue dynamique

Le menu langue dans le header affiche automatiquement les langues actives :

**Localisation :** [templates/backend/layouts/header.php:32](templates/backend/layouts/header.php:32)

```php
<li class="language-nav">
  <?php component('language-selector') ?>
</li>
```

**Composant :** [templates/backend/components/language-selector.php](templates/backend/components/language-selector.php)

Le composant :
- ✅ Charge dynamiquement depuis `config/languages.php`
- ✅ Affiche uniquement les langues actives
- ✅ Affiche la langue courante
- ✅ Permet le changement de langue avec redirection

## 🔄 Flux de fonctionnement

### Activation d'une langue

```
1. Utilisateur clique sur "Activer" pour ES (Espagnol)
   ↓
2. LanguageController::activate()
   ↓
3. Ajout de 'es' dans config('app.supported_locales')
   ↓
4. Mise à jour de config/app.php
   ↓
5. Création du fichier /languages/es.json (si manquant)
   ↓
6. Redirection avec message de succès
   ↓
7. ES apparaît dans le menu langue du header
```

### Changement de langue utilisateur

```
1. Utilisateur clique sur ES dans le menu langue
   ↓
2. JavaScript redirige vers ?lang=es
   ↓
3. SetLocaleMiddleware intercepte la requête
   ↓
4. Mise à jour de $_SESSION['locale'] = 'es'
   ↓
5. Page recharge avec traductions en espagnol
   ↓
6. Menu langue affiche ES comme langue courante
```

## 📊 Statuts des langues

### Active
- **Icône :** Badge vert "Active"
- **Signification :** Langue disponible pour les utilisateurs
- **Visibilité :** Apparaît dans le menu langue du header

### Par défaut
- **Icône :** Badge bleu "Par défaut" + étoile
- **Signification :** Langue utilisée par défaut
- **Usage :** Nouveaux utilisateurs, contenu public

### Fallback
- **Icône :** Badge orange "Fallback" + bouclier
- **Signification :** Langue de secours pour traductions manquantes
- **Usage :** Quand une clé n'existe pas dans la langue active

### Inactive
- **Icône :** Badge gris "Inactive"
- **Signification :** Langue désactivée
- **Visibilité :** N'apparaît pas dans le menu langue

## ⚙️ Actions disponibles

### Pour une langue active

| Action | Icône | Condition | Résultat |
|--------|-------|-----------|----------|
| Désactiver | ⏸️ | Pas par défaut ni fallback | Masque la langue |
| Définir par défaut | ⭐ | Pas déjà par défaut | Devient langue principale |
| Définir fallback | 🛡️ | Pas déjà fallback | Devient langue de secours |
| Gérer traductions | ✏️ | Toujours | Ouvre l'éditeur |
| Créer fichier | ➕ | Fichier manquant | Crée JSON |

### Pour une langue inactive

| Action | Icône | Résultat |
|--------|-------|----------|
| Activer | ▶️ | Rend la langue disponible |
| Créer fichier | ➕ | Crée le fichier JSON |
| Gérer traductions | ✏️ | Ouvre l'éditeur |

## 🚀 Exemples d'utilisation

### Exemple 1 : Ajouter l'espagnol

```
1. Accéder à /admin/settings/languages
2. Trouver "Spanish (Español)" dans la liste
3. Cliquer sur "Activer"
4. Créer le fichier avec "Créer le fichier"
5. Cliquer sur "Gérer les traductions"
6. Ajouter les traductions espagnoles
7. ES apparaît dans le menu langue
```

### Exemple 2 : Changer la langue par défaut

```
Situation : FR est par défaut, je veux EN

1. Accéder à /admin/settings/languages
2. Trouver "English (English)"
3. S'assurer qu'elle est active
4. Cliquer sur l'icône étoile "Définir par défaut"
5. Confirmer l'action
6. EN devient la langue par défaut
7. Les nouveaux visiteurs verront l'anglais
```

### Exemple 3 : Supprimer une langue

```
Situation : J'ai activé DE mais ne l'utilise pas

1. Accéder à /admin/settings/languages
2. Trouver "German (Deutsch)"
3. Vérifier qu'elle n'est ni par défaut ni fallback
4. Cliquer sur "Désactiver"
5. Confirmer
6. DE disparaît du menu langue
7. Le fichier languages/de.json reste (peut être réactivé)
```

## 🔒 Sécurité et restrictions

### Restrictions de désactivation

- ❌ **Langue par défaut** : Impossible à désactiver
  - Raison : Au moins une langue doit être active
  - Solution : Définir une autre langue par défaut d'abord

- ❌ **Langue de fallback** : Impossible à désactiver
  - Raison : Nécessaire pour les traductions manquantes
  - Solution : Définir une autre langue comme fallback d'abord

### Validation

- ✅ Vérification du code de langue avant activation
- ✅ Vérification de l'existence dans `config/languages.php`
- ✅ Empêche les codes invalides
- ✅ Empêche la duplication dans `supported_locales`

## 📝 Ajout d'une nouvelle langue personnalisée

Si une langue n'est pas dans la liste préconfigurée :

### 1. Ajouter dans config/languages.php

```php
'xy' => [
    'name' => 'Ma Langue',
    'native_name' => 'Native Name',
    'flag' => 'flag-icon-xy',  // Code du drapeau
    'short' => 'XY',
    'direction' => 'ltr'  // ou 'rtl'
],
```

### 2. Activer depuis l'interface

1. La langue apparaît automatiquement dans la liste
2. Suivre les étapes d'activation standard
3. Créer le fichier de traduction
4. Ajouter les traductions

## 🎓 Bonnes pratiques

### Langue par défaut
- ✅ Choisir la langue de votre audience principale
- ✅ Assurer que les traductions sont complètes
- ✅ Tester l'expérience utilisateur

### Langue de fallback
- ✅ Utiliser l'anglais (le plus universel)
- ✅ Maintenir les traductions à jour
- ✅ Couvrir toutes les clés importantes

### Fichiers de traduction
- ✅ Créer le fichier immédiatement après activation
- ✅ Copier depuis une langue similaire pour commencer
- ✅ Valider le JSON avant de sauvegarder
- ✅ Organiser les clés par module/section

### Maintenance
- ✅ Désactiver les langues non utilisées
- ✅ Surveiller les traductions manquantes
- ✅ Mettre à jour régulièrement
- ✅ Tester le changement de langue

## 🐛 Dépannage

### La langue n'apparaît pas dans le menu

**Causes possibles :**
1. Langue pas activée
2. Cache du navigateur
3. Erreur dans config/app.php

**Solution :**
```bash
1. Vérifier /admin/settings/languages
2. S'assurer que le badge "Active" est présent
3. Vider le cache du navigateur (Ctrl+F5)
4. Vérifier config/app.php : 'supported_locales'
```

### Le fichier de traduction n'est pas créé

**Causes possibles :**
1. Permissions du dossier /languages/
2. Erreur PHP

**Solution :**
```bash
# Vérifier les permissions
chmod 755 /languages/

# Créer manuellement
cp languages/en.json languages/xy.json
```

### Les traductions ne s'affichent pas

**Causes possibles :**
1. Fichier JSON invalide
2. Clés manquantes
3. Cache I18n

**Solution :**
```bash
# Valider le JSON
php -r "json_decode(file_get_contents('languages/xy.json'));"

# Vider le cache I18n
php sunu i18n:cache:clear
```

## 📚 Ressources

- [Documentation I18n](INTEGRATION_I18N.md)
- [Configuration des langues](config/languages.php)
- [Configuration de l'application](config/app.php)
- [Gestion des traductions](/admin/settings/translations)
- [Interface des langues](/admin/settings/languages)

## 🎉 Résumé

Le système de gestion des langues offre :

- ✅ **Interface simple** : Gestion visuelle complète
- ✅ **20+ langues** : Préconfigurées et prêtes à l'emploi
- ✅ **Activation rapide** : En un clic
- ✅ **Création automatique** : Fichiers de traduction générés
- ✅ **Menu dynamique** : Mise à jour automatique du header
- ✅ **Configuration flexible** : Langue par défaut et fallback
- ✅ **Sécurité** : Restrictions et validations

Pour toute question ou assistance, consultez la documentation ou contactez le support.
