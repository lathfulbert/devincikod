<?php

/**
 * Exemple Complet d'Utilisation du Système d'Autorisation
 * 
 * Ce fichier montre comment utiliser le système RBAC dans votre application
 */

// ============================================
// 1. CONFIGURATION INITIALE
// ============================================

// Charger les helpers d'autorisation
require_once __DIR__ . '/Core/Support/authorization_helpers.php';

// Enregistrer les middleware (dans votre bootstrap ou Application.php)
// $app->router->middleware('can', \App\Core\Middleware\PermissionMiddleware::class);
// $app->router->middleware('role', \App\Core\Middleware\RoleMiddleware::class);


// ============================================
// 2. DÉFINIR DES ABILITIES PERSONNALISÉES
// ============================================

$gate = gate();

// Ability: Éditer un post
$gate->define('update-post', function ($user, $post) {
    // Admin peut tout éditer
    if ($user->hasRole('admin')) {
        return true;
    }

    // Auteur peut éditer son propre post
    return $user->id === $post->user_id;
});

// Ability: Supprimer un post
$gate->define('delete-post', function ($user, $post) {
    // Seul admin peut supprimer
    return $user->hasRole('admin');
});

// Ability: Publier un post
$gate->define('publish-post', function ($user, $post) {
    if ($user->hasRole('admin')) {
        return true;
    }

    // Auteur avec permission
    return $user->id === $post->user_id && $user->can('publish-own-posts');
});

// Ability sans modèle
$gate->define('view-admin-panel', function ($user) {
    return $user->hasRole('admin') || $user->hasRole('moderator');
});


// ============================================
// 3. ROUTES PROTÉGÉES
// ============================================

// routes/web.php

use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Posts\Controllers\PostController;

// Protection par authentification seule
$router->get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Protection par permission
$router->get('/admin', [AdminController::class, 'index'])
    ->middleware('auth', 'can:manage-users');

// Protection par rôle
$router->get('/admin/settings', [SettingsController::class, 'index'])
    ->middleware('auth', 'role:admin');

// Plusieurs permissions (AND logic)
$router->post('/posts/publish', [PostController::class, 'publish'])
    ->middleware('auth', 'can:create-posts,publish-posts');

// Groupe avec middleware
$router->group(['middleware' => ['auth', 'can:manage-posts']], function ($router) {
    $router->get('/posts', [PostController::class, 'index']);
    $router->post('/posts', [PostController::class, 'store']);
    $router->put('/posts/{id}', [PostController::class, 'update']);
    $router->delete('/posts/{id}', [PostController::class, 'destroy']);
});

// Routes API avec protection
$router->group(['prefix' => '/api', 'middleware' => ['auth:api']], function ($router) {
    $router->get('/posts', [ApiPostController::class, 'index'])
        ->middleware('can:view-posts');

    $router->post('/posts', [ApiPostController::class, 'store'])
        ->middleware('can:create-posts');
});


// ============================================
// 4. DANS LES CONTROLLERS
// ============================================

class PostController extends Controller
{
    /**
     * Afficher un post
     */
    public function show($id)
    {
        $post = Post::find($id);

        // Vérifier avec can()
        if (cannot('view-post', $post)) {
            return $this->forbidden('You cannot view this post.');
        }

        return view('posts.show', ['post' => $post]);
    }

    /**
     * Mettre à jour un post
     */
    public function update($id)
    {
        $post = Post::find($id);

        // Méthode 1: Utiliser authorize() - Lance exception si refusé
        authorize('update-post', $post);

        // Update logic...
        $post->update($_POST);

        return redirect('/posts/' . $id)->with('success', 'Post updated!');
    }

    /**
     * Supprimer un post
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        // Méthode 2: Utiliser gate()->authorize()
        gate()->authorize('delete-post', $post);

        $post->delete();

        return redirect('/posts')->with('success', 'Post deleted!');
    }

    /**
     * Publier un post
     */
    public function publish($id)
    {
        $post = Post::find($id);

        // Méthode 3: Vérification manuelle avec can()
        if (cannot('publish-post', $post)) {
            if ($this->expectsJson()) {
                return $this->jsonError('Forbidden', 403);
            }
            return redirect()->back()->with('error', 'You cannot publish this post.');
        }

        $post->status = 'published';
        $post->published_at = date('Y-m-d H:i:s');
        $post->save();

        return redirect('/posts/' . $id)->with('success', 'Post published!');
    }

    /**
     * Vérifier plusieurs permissions
     */
    public function adminAction()
    {
        // Vérifier plusieurs permissions
        if (!gate()->all(['manage-users', 'manage-posts'])) {
            return $this->forbidden('You need all admin permissions.');
        }

        // Ou au moins une permission
        if (!gate()->any(['manage-users', 'manage-posts'])) {
            return $this->forbidden('You need at least one admin permission.');
        }

        // Action admin...
    }

    /**
     * Helper pour réponse forbidden
     */
    protected function forbidden($message)
    {
        if ($this->expectsJson()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Forbidden',
                'message' => $message,
                'code' => 403
            ]);
            exit;
        }

        return redirect('/403')->with('error', $message);
    }

    /**
     * Helper pour réponse JSON error
     */
    protected function jsonError($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $message,
            'code' => $code
        ]);
        exit;
    }

    /**
     * Vérifier si requête attend JSON
     */
    protected function expectsJson()
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'application/json') !== false ||
            strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
    }
}


// ============================================
// 5. DANS LES VUES
// ============================================

?>

<!-- Afficher bouton si permission -->
@can('edit-posts')
<a href="<?= route('posts.edit', ['id' => $post->id]) ?>" class="btn btn-primary">
    <i class="icon-edit"></i> Edit Post
</a>
@endcan

<!-- Afficher message si pas de permission -->
@cannot('edit-posts')
<p class="text-muted">
    <i class="icon-lock"></i>
    You don't have permission to edit posts.
</p>
@endcannot

<!-- Vérifier permission sur un modèle spécifique -->
<?php if (can('update-post', $post)): ?>
    <button onclick="editPost(<?= $post->id ?>)">Edit</button>
<?php endif; ?>

<!-- Combinaison avec @auth -->
@auth
@can('create-post')
<a href="<?= route('posts.create') ?>" class="btn btn-success">
    <i class="icon-plus"></i> New Post
</a>
@endcan

@cannot('create-post')
<div class="alert alert-info">
    <?= trans('messages.upgrade_to_create_posts') ?>
    <a href="<?= route('pricing') ?>">Upgrade Now</a>
</div>
@endcannot
@endauth

<!-- Menu conditionnel -->
<nav class="sidebar">
    <ul class="nav-menu">
        <li class="<?= is_active_route('dashboard.index') ?>">
            <a href="<?= route('dashboard.index') ?>">Dashboard</a>
        </li>

        @can('view-posts')
        <li class="<?= is_active_route('posts.*') ?>">
            <a href="<?= route('posts.index') ?>">Posts</a>
        </li>
        @endcan

        @can('manage-users')
        <li class="<?= is_active_route('users.*') ?>">
            <a href="<?= route('users.index') ?>">Users</a>
        </li>
        @endcan

        <?php if (can('view-admin-panel')): ?>
            <li class="<?= is_active_route('admin.*') ?>">
                <a href="<?= route('admin.index') ?>">
                    <i class="icon-shield"></i> Admin
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Actions sur un post -->
<div class="post-actions">
    @can('update-post', $post)
    <a href="<?= route('posts.edit', ['id' => $post->id]) ?>" class="btn btn-sm btn-primary">
        Edit
    </a>
    @endcan

    @can('delete-post', $post)
    <form action="<?= route('posts.destroy', ['id' => $post->id]) ?>" method="POST" style="display:inline;">
        <?= csrf_field() ?>
        <?= method_field('DELETE') ?>
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
            Delete
        </button>
    </form>
    @endcan

    @can('publish-post', $post)
    @if($post->status === 'draft')
    <form action="<?= route('posts.publish', ['id' => $post->id]) ?>" method="POST" style="display:inline;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-success">
            Publish
        </button>
    </form>
    @endif
    @endcan
</div>


<?php
// ============================================
// 6. DANS LE USER MODEL
// ============================================

class User extends Model
{
    /**
     * Check if user has a permission
     */
    public function can($permission, $model = null): bool
    {
        // Utiliser le Gate
        return gate()->forUser($this)->allows($permission, $model);
    }

    /**
     * Check if user does NOT have a permission
     */
    public function cannot($permission, $model = null): bool
    {
        return !$this->can($permission, $model);
    }

    /**
     * Check if user has a role
     */
    public function hasRole($role): bool
    {
        // Implémentation simple (à adapter selon votre DB)
        return $this->role === $role;

        // Ou avec relation many-to-many:
        // return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has a permission directly
     */
    public function hasPermission($permission): bool
    {
        // Admin a toutes les permissions
        if ($this->hasRole('admin')) {
            return true;
        }

        // Vérifier dans les permissions de l'utilisateur
        // return $this->permissions()->where('name', $permission)->exists();

        // Ou vérifier via les rôles
        // return $this->roles()->whereHas('permissions', function($q) use ($permission) {
        //     $q->where('name', $permission);
        // })->exists();

        return false;
    }

    /**
     * Assign a role to user
     */
    public function assignRole($role)
    {
        // Implémentation simple
        $this->role = $role;
        $this->save();

        // Ou avec relation:
        // $roleModel = Role::where('name', $role)->first();
        // $this->roles()->attach($roleModel->id);
    }

    /**
     * Give permission to user
     */
    public function givePermissionTo($permission)
    {
        // $permissionModel = Permission::where('name', $permission)->first();
        // $this->permissions()->attach($permissionModel->id);
    }
}


// ============================================
// 7. PAGE 403 FORBIDDEN
// ============================================
?>

<!-- templates/errors/403.php -->
@extends('layout')

@section('content')
<div class="error-page text-center">
    <div class="error-code">403</div>
    <h1>Forbidden</h1>
    <p class="error-message">
        <?= flash('_error') ?? 'You do not have permission to access this resource.' ?>
    </p>

    <div class="error-actions">
        <a href="<?= url('/') ?>" class="btn btn-primary">
            <i class="icon-home"></i> Go Home
        </a>

        @auth
        <a href="<?= url('/dashboard') ?>" class="btn btn-secondary">
            <i class="icon-grid"></i> Dashboard
        </a>
        @endauth

        @guest
        <a href="<?= url('/login') ?>" class="btn btn-secondary">
            <i class="icon-log-in"></i> Login
        </a>
        @endguest
    </div>
</div>
@endsection


<?php
// ============================================
// 8. ENREGISTREMENT DANS LE BOOTSTRAP
// ============================================

// bootstrap.php ou Core/Application.php

// Charger les helpers
require_once __DIR__ . '/Core/Support/helpers.php';
require_once __DIR__ . '/Core/Support/authorization_helpers.php';

// Enregistrer les middleware
$app->router->middleware('can', \App\Core\Middleware\PermissionMiddleware::class);
$app->router->middleware('role', \App\Core\Middleware\RoleMiddleware::class);

// Définir les abilities globales
$gate = gate();

$gate->define('update-post', function ($user, $post) {
    return $user->hasRole('admin') || $user->id === $post->user_id;
});

$gate->define('delete-post', function ($user, $post) {
    return $user->hasRole('admin');
});

$gate->define('view-admin-panel', function ($user) {
    return $user->hasRole('admin') || $user->hasRole('moderator');
});


// ============================================
// 9. TESTS
// ============================================

// Test des permissions
$user = User::find(1);

// Test can()
var_dump(can('edit-posts')); // bool

// Test cannot()
var_dump(cannot('delete-posts')); // bool

// Test authorize() - Lance exception si refusé
try {
    authorize('manage-users');
    echo "Autorisé!";
} catch (\App\Core\Exceptions\AuthorizationException $e) {
    echo "Refusé: " . $e->getMessage();
}

// Test gate()
$gate = gate();
var_dump($gate->allows('update-post', $post)); // bool
var_dump($gate->denies('delete-post', $post)); // bool

// Test any/all
var_dump($gate->any(['edit-posts', 'delete-posts'])); // bool
var_dump($gate->all(['edit-posts', 'delete-posts'])); // bool
?>