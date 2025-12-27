# Module WhatsApp Marketing

Ce module permet d'intégrer des fonctionnalités de marketing et de support via WhatsApp Business API dans l'application SunuFramework.

## Fonctionnalités

- **Gestion multi-passerelles** : Support pour Twilio, WATI, et un Mock (simulation) pour les tests.
- **Templates (Modèles)** : Synchronisation des modèles de messages approuvés par Meta/WhatsApp.
- **Campagnes** : Création et planification de campagnes de diffusion (Marketing).
- **Tableau de bord** : Suivi des statistiques d'envoi, de livraison et de lecture en temps réel.
- **Webhook** : Réception des statuts de livraison et des messages entrants.

## Installation et Activation

1.  **Copie des fichiers** : Le module doit être présent dans le dossier `Modules/WhatsAppMarketing`.
2.  **Migration Base de Données** :
    Exécutez la commande de migration pour créer les tables nécessaires (`whatsapp_gateways`, `whatsapp_templates`, `whatsapp_campaigns`, `whatsapp_messages`, `whatsapp_contacts`).
    ```bash
    php sunu migrate
    ```
3.  **Activation** :
    Le module devrait être détecté automatiquement. Si ce n'est pas le cas, assurez-vous qu'il est listé dans la table `modules` avec `is_active = 1`.

## Permissions (RBAC)

Le module installe les permissions suivantes, attribuées par défaut aux rôles `admin` et `owner` :

- `whatsapp.dashboard.view` : Accès au tableau de bord.
- `whatsapp.campaigns.view`, `create`, `edit`, `delete` : Gestion des campagnes.
- `whatsapp.templates.view`, `sync` : Gestion des templates.
- `whatsapp.gateways.view`, `manage` : Gestion des passerelles (Admin).
- `whatsapp.messages.view` : Historique des messages.

## Configuration

Pour commencer, rendez-vous dans **WhatsApp Marketing > Configuration** (`/admin/whatsapp/gateways`).

### Ajouter une passerelle

Cliquez sur "Ajouter une passerelle" et choisissez votre fournisseur :

- **Mock (Test)** : Utilisez ce fournisseur pour tester l'interface sans envoyer de vrais messages. Aucune clé API n'est requise.
- **Twilio** :
  - **Account SID** : Votre SID Twilio.
  - **Auth Token** : Votre jeton d'authentification.
  - **Numéro** : Votre numéro WhatsApp Twilio (ex: `+14155238886`).
- **WATI** :
  - **API Key** : Votre clé API WATI.
  - **Numéro** : Votre numéro WhatsApp associé.

## Utilisation

### 1. Synchronisation des Templates

Avant d'envoyer des campagnes, vous devez synchroniser vos templates. Les templates sont créés sur la plateforme de votre fournisseur (ex: Twilio Console ou Meta Business Manager) pour être approuvés par WhatsApp.

1.  Allez dans **WhatsApp Marketing > Modèles (Templates)**.
2.  Cliquez sur **"Sync depuis [Provider]"**.
3.  Les templates approuvés apparaîtront dans la liste.

### 2. Créer une Campagne

1.  Allez dans **WhatsApp Marketing > Campagnes**.
2.  Cliquez sur **"Créer une campagne"**.
3.  Nommez votre campagne.
4.  Sélectionnez un **Template** approuvé.
5.  Choisissez votre audience (pour l'instant "Tous les contacts opt-in").
6.  Planifiez l'envoi ou envoyez immédiatement.

### 3. Webhooks (Pour les développeurs)

Pour recevoir les statuts de livraison (envoyé, distribué, lu), vous devez configurer l'URL de webhook suivante chez votre fournisseur :

```
POST https://votre-domaine.com/api/v1/whatsapp/webhook/{GATEWAY_ID}
```

Remplacez `{GATEWAY_ID}` par l'ID de votre passerelle dans la base de données.

## Développement

### Envoyer un message par code

Vous pouvez utiliser le service `WhatsAppService` n'importe où dans votre code :

```php
use Modules\WhatsAppMarketing\Services\WhatsAppService;

$service = new WhatsAppService();

// Envoyer un message texte simple (Session message, fenêtre 24h)
$service->sendText('+2250707070707', 'Bonjour, ceci est un test.');

// Envoyer un template (Marketing/Utility/Auth)
$components = [
    [
        'type' => 'body',
        'parameters' => [
            ['type' => 'text', 'text' => 'Jean'], // Variable {{1}}
            ['type' => 'text', 'text' => '12345']  // Variable {{2}}
        ]
    ]
];

$service->sendTemplate('+2250707070707', 'nom_du_template', 'fr', $components);
```
