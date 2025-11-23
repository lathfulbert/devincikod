# Module Akpa - SunuFramework

## Description

Module de démonstration pour tester le système d'installation de modules via ZIP.

Ce module implémente un système de akpa simple avec :

- Gestion des articles (CRUD)
- Interface d'administration
- Migration de base de données
- Menu dans le sidebar

## Installation

### Via l'interface d'administration

1. Accédez à `/admin/modules/upload`
2. Sélectionnez le fichier `Akpa.zip`
3. Cliquez sur "Installer le module"
4. Activez le module depuis `/admin/modules`

### Manuellement

1. Décompressez le contenu dans `Modules/Akpa/`
2. Exécutez les migrations : `php sunu migrate --up`
3. Activez le module depuis l'interface

## Utilisation

Une fois installé et activé, le module ajoute un menu "Akpa" dans le sidebar avec :

- **Articles** : Liste des articles
- **Ajouter** : Créer un nouvel article

## Fonctionnalités

- ✅ CRUD complet pour les articles
- ✅ Interface backend intégrée
- ✅ Migration de base de données
- ✅ Flash messages
- ✅ Protection CSRF
- ✅ Menu dynamique dans le sidebar

## Routes

- `GET /admin/akpa` - Liste des articles
- `GET /admin/akpa/create` - Formulaire de création
- `POST /admin/akpa/store` - Enregistrer un article
- `GET /admin/akpa/{id}/edit` - Formulaire d'édition
- `POST /admin/akpa/{id}/update` - Mettre à jour
- `POST /admin/akpa/{id}/delete` - Supprimer

## Structure

```
Akpa/
├── module.json                     # Manifest du module
├── AkpaModule.php                  # Classe principale
├── Controllers/
│   └── AkpaController.php          # Contrôleur CRUD
├── Views/
│   ├── index.php                   # Liste
│   ├── create.php                  # Création
│   └── edit.php                    # Édition
├── Database/
│   └── Migrations/
│       └── 001_CreateAkpaPostsTable.php
└── README.md                       # Ce fichier
```

## Auteur

SunuFramework Team

## Licence

MIT
