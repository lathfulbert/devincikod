# Documentation des Pages d'Erreur - SunuFramework2

## Vue d'ensemble

Le système de gestion des erreurs de SunuFramework2 fournit des pages d'erreur personnalisées et élégantes pour tous les codes d'erreur HTTP courants (401, 403, 404, 500, etc.). Le système capture automatiquement les erreurs et les exceptions non gérées et affiche des pages d'erreur conviviales.

## Fonctionnalités

- ✅ Pages d'erreur personnalisées pour les codes HTTP courants
- ✅ Gestion automatique des exceptions non capturées
- ✅ Mode debug avec affichage détaillé des erreurs
- ✅ Design responsive basé sur le template Cuba
- ✅ Intégration complète avec le framework
- ✅ Fonctions helper pour déclencher des erreurs facilement

## Architecture

### Structure des fichiers

```
Views/errors/
├── layout.php          # Layout commun pour toutes les pages d'erreur
├── 401.php            # Non authentifié
├── 403.php            # Accès interdit
├── 404.php            # Page introuvable
└── 500.php            # Erreur serveur

Core/
├── Routing/Router.php              # Contient handleError()
├── Exceptions/ExceptionHandler.php # Gestionnaire global d'exceptions
└── Support/helpers.php             # Fonctions abort(), abort_if(), abort_unless()
```

### Composants principaux

#### 1. ExceptionHandler (`Core/Exceptions/ExceptionHandler.php`)

Gère toutes les exceptions et erreurs PHP non capturées :

```php
class ExceptionHandler
{
    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function render(Throwable $e): void
    {
        // Affiche la page 500 avec ou sans détails selon APP_DEBUG
    }
}
```

#### 2. Router::handleError() (`Core/Routing/Router.php`)

Méthode statique pour afficher les pages d'erreur :

```php
public static function handleError(
    int $code,
    string $title = 'Erreur',
    ?string $message = null
): void
{
    http_response_code($code);

    // Cherche la vue d'erreur correspondante
    $viewPath = BASE_PATH . '/Views/errors/' . $code . '.php';

    if (file_exists($viewPath)) {
        // Rend la page avec le layout
        include $viewPath;
        include layout.php;
    } else {
        // Fallback HTML basique
    }

    exit;
}
```

#### 3. Layout des erreurs (`Views/errors/layout.php`)

Template HTML commun pour toutes les pages d'erreur avec :
- Design moderne avec gradient violet
- Responsive design (Bootstrap)
- Icônes Feather
- Animations CSS

## Utilisation

### 1. Déclencher une erreur 404

```php
// Dans un contrôleur
public function show($id)
{
    $item = Item::find($id);

    if (!$item) {
        abort(404); // Affiche la page 404
    }

    return view('items.show', ['item' => $item]);
}
```

### 2. Déclencher une erreur 403

```php
// Vérifier les permissions
public function edit($id)
{
    $post = Post::find($id);

    abort_unless(
        $post->user_id === auth()->id(),
        403,
        "Vous n'êtes pas autorisé à modifier cet article"
    );

    return view('posts.edit', ['post' => $post]);
}
```

### 3. Déclencher une erreur 401

```php
// Middleware d'authentification
public function handle()
{
    if (!isset($_SESSION['user'])) {
        abort(401); // Redirige vers page de connexion
    }

    return true;
}
```

### 4. Erreurs 500 automatiques

Les erreurs 500 sont déclenchées automatiquement pour toute exception non capturée :

```php
public function process()
{
    // Si cette ligne lance une exception,
    // la page 500 sera affichée automatiquement
    $result = $this->riskyOperation();
}
```

### 5. Utilisation conditionnelle

```php
// abort_if : déclenche si la condition est vraie
abort_if($user->banned, 403, "Votre compte a été suspendu");

// abort_unless : déclenche si la condition est fausse
abort_unless($user->verified, 403, "Veuillez vérifier votre email");
```

## API des fonctions Helper

### `abort(int $code, ?string $message = null): void`

Déclenche une page d'erreur avec le code HTTP spécifié.

**Paramètres :**
- `$code` (int) : Code HTTP (401, 403, 404, 500, etc.)
- `$message` (string|null) : Message d'erreur optionnel (affiché en mode debug pour 500)

**Exemple :**
```php
abort(404);
abort(403, "Accès refusé à cette ressource");
abort(500, "Erreur lors du traitement des données");
```

### `abort_if(bool $condition, int $code, ?string $message = null): void`

Déclenche une erreur si la condition est vraie.

**Exemple :**
```php
abort_if($post->deleted, 404, "Cet article a été supprimé");
abort_if(!$user->isAdmin(), 403);
```

### `abort_unless(bool $condition, int $code, ?string $message = null): void`

Déclenche une erreur si la condition est fausse.

**Exemple :**
```php
abort_unless($user->canEdit($post), 403);
abort_unless($file->exists(), 404, "Fichier introuvable");
```

## Configuration

### Mode Debug

Le mode debug est contrôlé par la variable d'environnement `APP_DEBUG` dans le fichier `.env` :

```env
# Mode production (erreurs génériques)
APP_DEBUG=false

# Mode développement (détails complets des erreurs)
APP_DEBUG=true
```

**En mode debug (APP_DEBUG=true) :**
- Les pages 500 affichent la stack trace complète
- Les messages d'erreur détaillés sont visibles
- Informations sur le fichier et la ligne d'erreur

**En mode production (APP_DEBUG=false) :**
- Messages d'erreur génériques uniquement
- Pas de détails techniques exposés
- Sécurité renforcée

## Personnalisation

### Modifier le design des pages d'erreur

Éditez les fichiers dans `Views/errors/` :

```php
// Views/errors/404.php
<div class="container">
    <div class="error-code">
        <h1>404</h1>
    </div>
    <div class="col-md-8 offset-md-2">
        <h3>Votre titre personnalisé</h3>
        <p class="sub-content">Votre message personnalisé</p>
    </div>
    <div>
        <a class="btn btn-primary btn-lg" href="<?= url('/') ?>">
            VOTRE BOUTON
        </a>
    </div>
</div>
```

### Modifier le layout global

Éditez `Views/errors/layout.php` pour changer :
- Le gradient de fond (ligne 30)
- Les polices
- Les styles CSS
- Le titre du site

```php
// Changer le gradient de fond
background: linear-gradient(135deg, #votre-couleur1 0%, #votre-couleur2 100%);
```

### Ajouter de nouveaux codes d'erreur

1. Créez un nouveau fichier dans `Views/errors/` :

```php
// Views/errors/429.php (Too Many Requests)
<div class="container">
    <div class="error-code">
        <h1>429</h1>
    </div>
    <div class="col-md-8 offset-md-2">
        <h3>Trop de requêtes</h3>
        <p class="sub-content">
            Vous avez effectué trop de requêtes. Veuillez patienter.
        </p>
    </div>
</div>
```

2. Mettez à jour la fonction `abort()` dans `helpers.php` :

```php
function abort(int $code, ?string $message = null): void
{
    \App\Core\Routing\Router::handleError($code, match($code) {
        401 => 'Non Authentifié',
        403 => 'Accès Interdit',
        404 => 'Page Introuvable',
        429 => 'Trop de requêtes',  // NOUVEAU
        500 => 'Erreur Serveur',
        default => 'Erreur',
    }, $message);
}
```

## Intégration avec le Router

Le système est automatiquement intégré au Router :

```php
// Core/Routing/Router.php
protected function handleNotFound(): void
{
    $this->handleError(404, 'Page Introuvable');
}
```

Toutes les routes non trouvées déclenchent automatiquement la page 404.

## Gestion des erreurs dans les middlewares

```php
class AuthMiddleware
{
    public function handle($request, $next)
    {
        if (!$this->isAuthenticated()) {
            abort(401); // Page 401 automatique
        }

        return $next($request);
    }
}
```

## Logging des erreurs

Toutes les erreurs sont automatiquement loguées via `ExceptionHandler::report()` :

```php
public function report(Throwable $e): void
{
    logger()->error($e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
    ]);
}
```

Les logs sont stockés dans `storage/logs/`.

## Bonnes pratiques

### 1. Utiliser les bons codes d'erreur

- **401** : L'utilisateur n'est pas connecté
- **403** : L'utilisateur est connecté mais n'a pas les droits
- **404** : La ressource demandée n'existe pas
- **500** : Erreur interne du serveur (ne jamais déclencher manuellement)

### 2. Fournir des messages contextuels

```php
// ❌ Pas assez d'information
abort(403);

// ✅ Message clair
abort(403, "Seuls les administrateurs peuvent accéder à cette page");
```

### 3. Ne pas exposer d'informations sensibles

```php
// ❌ Mauvais : expose la structure de la DB
abort(500, "Table 'users' doesn't exist");

// ✅ Bon : message générique (les détails sont dans les logs)
abort(500, "Erreur lors de la récupération des données");
```

### 4. Utiliser abort_if/abort_unless pour la lisibilité

```php
// ❌ Moins lisible
if (!$user->canAccess($resource)) {
    abort(403);
}

// ✅ Plus concis et clair
abort_unless($user->canAccess($resource), 403);
```

## Dépannage

### La page d'erreur n'apparaît pas

Vérifiez que :
1. `BASE_PATH` est défini dans `Application::__construct()`
2. Les fichiers existent dans `Views/errors/`
3. Le ExceptionHandler est enregistré dans `Application::__construct()`

### Les styles ne s'affichent pas

Vérifiez que :
1. Les assets CSS existent dans `public/assets/css/`
2. La fonction `asset()` retourne les bons chemins
3. Le serveur web sert correctement le dossier `public/`

### Les erreurs 500 montrent trop de détails

Définissez `APP_DEBUG=false` dans votre fichier `.env` pour le mode production.

## Exemples complets

### Contrôleur avec gestion d'erreurs

```php
class PostController
{
    public function show($id)
    {
        $post = Post::find($id);

        // Vérifie que le post existe
        abort_if(!$post, 404, "Article #{$id} introuvable");

        // Vérifie que le post est publié
        abort_if($post->status !== 'published', 404);

        // Vérifie les permissions
        abort_unless(
            $post->isPublic() || auth()->id() === $post->user_id,
            403,
            "Cet article est privé"
        );

        return view('posts.show', ['post' => $post]);
    }

    public function delete($id)
    {
        $post = Post::find($id);
        abort_unless($post, 404);

        // Seul l'auteur ou un admin peut supprimer
        $canDelete = auth()->id() === $post->user_id || auth()->isAdmin();
        abort_unless($canDelete, 403, "Vous ne pouvez pas supprimer cet article");

        $post->delete();
        redirect('/posts');
    }
}
```

### Middleware personnalisé avec erreurs

```php
class RoleMiddleware
{
    public function handle($request, $next, ...$roles)
    {
        // Vérifie l'authentification
        if (!isset($_SESSION['user'])) {
            abort(401);
        }

        // Vérifie le rôle
        $userRole = $_SESSION['user']['role'] ?? null;

        abort_unless(
            in_array($userRole, $roles),
            403,
            "Accès réservé aux rôles : " . implode(', ', $roles)
        );

        return $next($request);
    }
}
```

## Support

Pour plus d'informations :
- Voir la documentation du framework dans `/docs`
- Consulter les exemples dans `/examples/html/error_pages`
- Vérifier les logs dans `/storage/logs`

---

**Version:** 1.0
**Dernière mise à jour:** 2025-12-07
**Framework:** SunuFramework2
