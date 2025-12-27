# Documentation de SunuFramework

Bienvenue dans la documentation officielle de **SunuFramework**. Ce framework PHP est conçu pour être modulaire, performant et facile à utiliser, s'inspirant des meilleures pratiques de Laravel tout en restant léger.

## Table des Matières

1. [Introduction](#1-introduction)
2. [Installation et Configuration](#2-installation-et-configuration)
3. [Structure des Dossiers](#3-structure-des-dossiers)
4. [Architecture du Noyau (Core)](#4-architecture-du-noyau-core)
5. [Le Conteneur de Services](#5-le-conteneur-de-services)
6. [Système de Routage](#6-système-de-routage)
7. [Contrôleurs (MVC)](#7-contrôleurs-mvc)
8. [Vues et Moteur de Template](#8-vues-et-moteur-de-template)
9. [Base de Données et ORM](#9-base-de-données-et-orm)
10. [Sécurité](#10-sécurité)

---

## 1. Introduction

SunuFramework est un framework PHP moderne basé sur une architecture MVC (Modèle-Vue-Contrôleur) et orienté modules. Il intègre nativement des fonctionnalités essentielles telles que :

- Un conteneur d'injection de dépendances puissant.
- Un système de routage flexible.
- Une architecture modulaire permettant d'étendre facilement les fonctionnalités.
- Une couche d'abstraction de base de données.
- Un moteur de template intégré.

## 2. Installation et Configuration

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- Serveur Web (Apache/Nginx) ou serveur de développement PHP

### Installation

Cloner le dépôt et installer les dépendances :

```bash
git clone <url-du-repo>
cd sunuframework3
composer install
```

### Configuration

1. Dupliquez le fichier `.env.example` en `.env` :
   ```bash
   cp .env.example .env
   ```
2. Générez la clé d'application (requise pour la sécurité) :
   ```bash
   php sunu key:generate
   ```
3. Configurez votre base de données dans le fichier `.env` :
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=votre_base
   DB_USERNAME=root
   DB_PASSWORD=
   ```

---

## 3. Structure des Dossiers

Voici un aperçu de l'arborescence du projet :

- **`App/`** : Le cœur de votre logique métier peut résider ici, bien que l'approche modulaire soit privilégiée.
- **`Core/`** : Le noyau du framework. Contient les classes système (Application, Router, Database, View, etc.). **Ne pas modifier ce dossier** sauf si vous contribuez au framework.
- **`Modules/`** : Le dossier principal pour vos fonctionnalités. Chaque module (ex: `Auth`, `Dashboard`, `Users`) est isolé avec ses propres routes, contrôleurs, vues et migrations.
- **`config/`** : Contient tous les fichiers de configuration de l'application.
- **`public/`** : Le point d'entrée unique (Document Root). Contient `index.php` et les assets (CSS, JS, images).
- **`resources/`** : Contient les vues globales, les fichiers de langue et les assets bruts.
- **`routes/`** : Définition des routes globales (`web.php`, `api.php`).
- **`storage/`** : Cache, logs, et fichiers générés.
- **`vendor/`** : Dépendances Composer.

---

## 4. Architecture du Noyau (Core)

### Cycle de Vie

Tout commence par le fichier `public/index.php`.

1. **Autoloading** : Chargement des classes via Composer.
2. **Initialisation** : Création de l'instance `App\Core\Application`.
3. **Bootstrapping (`$app->boot()`)** :
   - Chargement des variables d'environnement (`.env`).
   - Initialisation de la configuration.
   - Connexion à la base de données.
   - Démarrage de la session.
   - Chargement des middlewares globaux.
   - **Découverte et chargement des Modules**.
   - Chargement des routes.
4. **Exécution (`$app->run()`)** :
   - Analyse de la requête entrante.
   - Dispatching via le Routeur.
   - Exécution du Contrôleur ou de la Closure.
   - Envoi de la réponse au navigateur.

### Le Système de Modules

SunuFramework utilise un `ModuleManager` (`App\Core\Module\ModuleManager`) pour gérer les extensions.

- Les modules sont automatiquement découverts dans le dossier `Modules/`.
- Chaque module peut avoir un fichier `module.json` pour sa configuration.
- Les routes des modules sont chargées automatiquement si elles suivent les conventions.

---

## 5. Le Conteneur de Services

Le framework repose sur un conteneur d'injection de dépendances (`App\Core\Container\Container`), similaire à celui de Laravel.

### Utilisation

Vous pouvez accéder au conteneur via l'instance globale de l'application ou via l'injection de dépendances dans vos classes.

#### Binding (Liaison)

```php
// Lier une interface à une implémentation
$app->bind(UserRepositoryInterface::class, MySQLUserRepository::class);

// Lier un singleton (instance unique partagée)
$app->singleton(Database::class, function() {
    return new Database(/* config */);
});
```

#### Résolution

```php
// Résoudre manuellement
$userRepo = $app->make(UserRepositoryInterface::class);

// Injection automatique (dans les contrôleurs, par exemple)
public function index(UserRepositoryInterface $userRepo) {
    // $userRepo est automatiquement injecté
}
```

---

## 6. Système de Routage

Les routes sont définies dans `routes/web.php` (pour le web) et `routes/api.php` (pour l'API), ainsi que dans les fichiers de routes des modules.

### Définition des Routes

```php
// Route simple avec Closure
$router->get('/home', function() {
    return 'Bienvenue !';
});

// Route vers un Contrôleur (Syntaxe Array)
$router->get('/users', [UserController::class, 'index']);

// Routes avec verbes HTTP
$router->post('/users', [UserController::class, 'store']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'delete']);
```

### Paramètres de Route

Les paramètres sont définis entre accolades `{}`.

```php
$router->get('/users/{id}', function($id) {
    return "Utilisateur ID: " . $id;
});
```

### Groupes et Middleware

Vous pouvez grouper des routes pour leur appliquer des préfixes ou des middlewares communs.

```php
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'role:admin']], function($router) {
    $router->get('/dashboard', [AdminController::class, 'dashboard']);
});
```

---

## 7. Contrôleurs (MVC)

Les contrôleurs sont des classes PHP standard qui gèrent la logique de la requête. Ils sont généralement placés dans `Modules/{Module}/Controllers` ou `App/Controllers`.

### Injection de Dépendances

Le conteneur injecte automatiquement les dépendances définies dans le constructeur ou les méthodes du contrôleur.

```php
namespace Modules\User\Controllers;

use App\Core\Request;
use Modules\User\Models\User;

class UserController
{
    public function show($id)
    {
        $user = User::find($id);
        return view('user.profile', ['user' => $user]);
    }
}
```

---

## 8. Vues et Moteur de Template

SunuFramework utilise un moteur de template inspiré de Blade (Laravel). Les fichiers de vue se terminent par `.php` ou `.tpl` et sont stockés dans `resources/views` ou dans le dossier `Views` des modules.

### Rendu d'une Vue

```php
// Depuis un contrôleur ou une route
return view('welcome', ['name' => 'John']);
```

Le framework cherche la vue dans cet ordre :

1. `resources/views/welcome.php`
2. `Modules/{CurrentModule}/Views/welcome.php`

### Syntaxe du Template

#### Héritage (Layouts)

**layout.php**

```html
<html>
  <body>
    <div class="content"><?php $this->yieldSection('content'); ?></div>
  </body>
</html>
```

**page.php**

```php
<?php $this->setExtends('layout'); ?>

<?php $this->startSection('content'); ?>
    <h1>Ma Page</h1>
    <p>Contenu spécifique.</p>
<?php $this->endSection(); ?>
```

---

## 9. Base de Données et ORM

SunuFramework inclut un ORM léger (`App\Core\Database\Model`) pour interagir avec la base de données.

### Création d'un Modèle

Créez une classe qui étend `App\Core\Database\Model`.

```php
namespace Modules\User\Models;

use App\Core\Database\Model;

class User extends Model
{
    // Table associée (défaut: users)
    protected static string $table = 'users';

    // Attributs castés
    protected array $casts = [
        'is_active' => 'boolean',
        'settings' => 'array'
    ];
}
```

### Opérations CRUD

```php
// Récupérer tous les enregistrements
$users = User::all();

// Trouver par ID
$user = User::find(1);

// Créer
$newUser = new User(['name' => 'Alice', 'email' => 'alice@example.com']);
$newUser->save();

// Mettre à jour
$user->name = 'Alice Smith';
$user->save();

// Supprimer
$user->delete();
```

### Relations

L'ORM supporte `hasOne`, `hasMany`, `belongsTo`, `belongsToMany`.

```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

---

## 10. Sécurité

### Protection CSRF

Le framework inclut une protection automatique contre les failles CSRF pour les requêtes POST, PUT, DELETE.
Assurez-vous d'inclure le champ token dans vos formulaires HTML :

```html
<form method="POST" action="/profile">
  <?= csrf_field() ?>
  <!-- champs... -->
</form>
```

### Authentification

Le module `Auth` (si installé) gère l'authentification des utilisateurs. Utilisez le middleware `auth` pour protéger vos routes.
