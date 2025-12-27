# LathDevinci Framework  

Ajouter un système de Cron / Scheduler

Ajoutery securite (Can(), authorization)
Ajouter un exemple d’ORM complet (Model + QueryBuilder)
✅ Ajouter un système de Cron / Scheduler
✅ Ajouter un exemple de Middlewares avancés
✅ Générer un tutoriel pas-à-pas pour créer un module professionnel
✅ Générer la version PDF ou ZIP téléchargeable de la documentation
✅ Ajouter un exemple complet d’authentification (Login/Register)
➡️ Dites-moi simplement ce que vous voulez ajouter ensuite.

Verification de mon ORM
🔧 Sauvegarde (save), update(), delete(), create()
🔧 Relations ORM (hasOne, hasMany, belongsTo)
🔧 Scopes
🔧 Pagination
🔧 Eager loading
🔧 Casting (int, bool, datetime)
🔧 Soft Deletes
🔧 Events (creating, created, updating...)

Framework PHP modulaire — Core + Modules autonomes + Templates + Permissions + Multilingue (i18n)

---

# 🚀 Introduction

**LathDevinci Framework** est un framework PHP conçu **from scratch**, inspiré de Laravel, WordPress et des CMS modulaires modernes.  
Il repose sur un **Core solide** respectant les principes **SOLID**, capable de :

- Charger automatiquement des **modules autonomes** (Blog, Ecommerce, CRM, Auth…)
- Isoler entièrement chaque module (namespace, routes, services, assets, migrations)
- Activer ou désactiver les modules à la demande
- Gérer des **templates frontend et backend** interchangeables
- Fournir un système d'authentification moderne
- Gérer les permissions via la méthode **can()**
- Offrir un système multilingue complet (i18n)
- Centraliser la configuration, base de données, cache, routing, middlewares, sécurité

Son objectif : un framework personnalisable, extensible, élégant et professionnel.

---

# 📦 Structure du Framework

```
/core
    /Bootstrap
    /Routing
    /Support
    /Database
    /Cache
    /Security
    /Auth
    /I18n
    Core.php
    ModuleManager.php

/modules
    /Blog
        /Routes
        /Controllers
        /Models
        /Views
        /Migrations
        /Assets
        module.json

/templates
    /default
        /views
        /layouts
        /assets
    /admin

/config
/lang
public
vendor
```

---

# ⚙️ 1. Le Core

Le Core centralise toute la mécanique du framework :

- Cycle de vie des modules
- Autochargement PSR-4
- Routing HTTP
- Gestion des templates
- Base de données & migrations
- Configuration
- Cache
- Middlewares
- Sécurité (CSRF, sanitization)
- Authentification + Permissions (can)
- Multilingue (i18n)

---

# 🧩 2. ModuleManager

Chaque module est défini par un fichier :

### `module.json`
```json
{
  "name": "Blog",
  "version": "1.0",
  "enabled": true,
  "namespace": "Modules\Blog",
  "providers": [
    "Modules\Blog\BlogServiceProvider"
  ]
}
```

Le Core charge automatiquement :

- Routes  
- Migrations  
- Services  
- Permissions  
- Vues  
- Assets  
- Fichiers de config  

---

# 🧱 3. ModuleContract (Contrat Standard)

Chaque module implémente :

```php
interface ModuleContract {
    public function register();
    public function boot();
    public function routes();
    public function migrations();
    public function permissions();
}
```

---

# 🚦 4. Routing System

Router minimaliste inspiré de Laravel :

```php
Route::get('/blog', 'BlogController@index');
Route::post('/blog', 'BlogController@store');
```

---

# 🔐 5. Authentification & Permissions (can)

```php
if (Auth::can('blog.edit')) {
    // autorisé
}
```

---

# 🌍 6. Multilingue (i18n)

```
/lang
    /fr
        messages.php
    /en
        messages.php
    /es
        messages.php
```

---

# 🎨 7. Templates

```
/templates
    /default
        /views
        /layouts
        /assets
    /admin
```

---

# 🧰 8. Services du Core

Cache, Database, Config, Filesystem, Helpers, Security, Middlewares.

---

# 🧱 10. SOLID

Respect intégral des principes SOLID.

---

# 🛠️ 12. Installation

```
composer install
php core migrate
php core serve
```

---
