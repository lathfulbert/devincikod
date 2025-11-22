# Logging & Monitoring - Phase 2 Documentation

## ✅ Nouveautés Phase 2

La Phase 2 ajoute la gestion robuste des erreurs et le stockage en base de données.

## 📦 Composants Ajoutés

### Exception Handling

- ✅ `Core/Exceptions/ExceptionHandler.php` - Gestionnaire global
- ✅ Intégration automatique dans `Core/Application.php`

### Database Logging

- ✅ `Core/Logging/Handlers/DatabaseHandler.php` - Handler SQL
- ✅ `Modules/Admin/Database/Migrations/..._create_logs_table.php` - Migration
- ✅ Support du driver `database` dans `LogManager`

### Middleware

- ✅ `Core/Logging/Middleware/HttpRequestLogger.php` - Log automatique des requêtes

## 🚀 Installation & Mise à jour

### 1. Exécuter la migration

Pour activer le logging en base de données :

```bash
php sunu migrate
```

### 2. Configurer le canal Database (Optionnel)

Dans `.env`, vous pouvez changer le canal par défaut :

```env
LOG_CHANNEL=database
```

Ou utiliser le canal `stack` (à venir) pour logger dans les deux.

## 📝 Utilisation Avancée

### Gestion des Exceptions

Le framework capture maintenant automatiquement :

- Les erreurs PHP fatales (E_ERROR, etc.)
- Les exceptions non attrapées
- Les erreurs de syntaxe (shutdown function)

Tout est logué avec :

- Stack trace complet
- URL et Méthode HTTP
- Fichier et ligne

### Logging en Base de Données

```php
// Explicite
logger('database')->info('Action utilisateur', ['user_id' => 1]);

// Via config (si LOG_CHANNEL=database)
logger()->info('Action utilisateur');
```

Les logs en base de données contiennent :

- `channel`, `level`, `message`
- `context` (JSON)
- `extra` (JSON avec IP, User Agent)
- `created_at`

### Middleware HTTP

Pour activer le logging de toutes les requêtes, ajoutez le middleware dans `Core/Routing/Router.php` ou globalement.

```php
// Exemple d'utilisation manuelle pour une route spécifique
Router::middleware([\App\Core\Logging\Middleware\HttpRequestLogger::class]);
```

## 📊 Prochaines Étapes (Monitoring Dashboard)

Maintenant que nous avons :

1. Un système de log robuste (Phase 1)
2. Le stockage en base de données (Phase 2)
3. La capture des exceptions (Phase 2)

Nous sommes prêts pour la **Phase 3 : Dashboard de Monitoring**.

Le dashboard pourra simplement lire la table `logs` pour afficher :

- Les erreurs récentes
- Les requêtes lentes (loguées par le middleware)
- Les actions utilisateurs

---

**Système de Logging & Exception Handling : 100% Opérationnel** 🚀
