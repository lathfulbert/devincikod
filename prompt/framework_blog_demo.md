# Système de Blog Complet pour Framework

## Objectif
Mettre en place un système de blog complet dans le framework pour démontrer son bon fonctionnement. Utilisation du module **Image** pour la gestion des images et du module **Admin** pour les menus CRUD.

---

## Modules et Dépendances
- **Blog Module** : gestion des articles, catégories et tags.
- **Image Module** : gestion des images (upload, redimensionnement, thumbnails, suppression).
- **Admin Module** : CRUD complet pour articles, catégories, tags et gestion des utilisateurs.
- **Auth & Security** : restriction d’accès aux pages admin et permissions par rôle.
- **Front-end** : affichage responsive des articles avec pagination et filtres par catégorie/tag.

---

## Base de Données

### Table `articles`
| Colonne | Type | Détails |
|---------|------|---------|
| id | PK | Identifiant unique |
| title | string | Titre de l'article |
| slug | string | URL unique |
| content | text | Contenu de l'article |
| image_id | FK | Référence à `images` |
| category_id | FK | Référence à `categories` |
| created_at | timestamp | Création |
| updated_at | timestamp | Mise à jour |

### Table `categories`
| Colonne | Type | Détails |
|---------|------|---------|
| id | PK | Identifiant unique |
| name | string | Nom de la catégorie |
| slug | string | URL unique |
| created_at | timestamp | Création |
| updated_at | timestamp | Mise à jour |

### Table `tags`
| Colonne | Type | Détails |
|---------|------|---------|
| id | PK | Identifiant unique |
| name | string | Nom du tag |
| slug | string | URL unique |
| created_at | timestamp | Création |
| updated_at | timestamp | Mise à jour |

### Table pivot `article_tag`
| Colonne | Type | Détails |
|---------|------|---------|
| article_id | FK | Référence à l'article |
| tag_id | FK | Référence au tag |

---

## Fonctionnalités

### Front-end
- Liste des articles avec pagination.
- Filtrage par catégorie ou tag.
- Affichage d’un article complet avec image et tags.
- Recherche par titre/contenu.

### Admin Panel
- CRUD complet pour articles, catégories et tags.
- Upload et gestion des images via le module Image.
- Association d’images et tags aux articles.
- Gestion des rôles et permissions.

### Image Management
- Upload depuis le formulaire admin.
- Redimensionnement automatique.
- Génération de thumbnails.
- Suppression sécurisée des images associées.

---

## Routes Exemples

### Front-end
- `/blog` → liste des articles.
- `/blog/{slug}` → affichage d’un article.
- `/blog/category/{slug}` → articles d’une catégorie.
- `/blog/tag/{slug}` → articles avec un tag spécifique.

### Admin
- `/admin/blog/articles` → gestion des articles.
- `/admin/blog/categories` → gestion des catégories.
- `/admin/blog/tags` → gestion des tags.
- `/admin/blog/images` → gestion des images.

---

## Exemple d’implémentation

```php
// ArticleController.php
class ArticleController extends Controller {
    public function index() {
        $articles = Article::with('category', 'tags', 'image')->paginate(10);
        return view('blog.index', compact('articles'));
    }

    public function show($slug) {
        $article = Article::with('category', 'tags', 'image')->where('slug', $slug)->firstOrFail();
        return view('blog.show', compact('article'));
    }
}

// Admin Article CRUD
class AdminArticleController extends AdminController {
    public function create() { return view('admin.blog.create'); }

    public function store(Request $request) {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required',
            'category_id' => 'required|integer',
        ]);

        if ($request->hasFile('image')) {
            $data['image_id'] = Image::upload($request->file('image'));
        }

        $article = Article::create($data);
        $article->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article créé !');
    }
}
```

---

## Bonus Démonstration
- Articles de démonstration et images sample.
- Statistiques : nombre d’articles, catégories, tags et images.
- Possibilité d’activer/désactiver les articles depuis l’admin.

---

Ce fichier sert de **guide complet** pour déployer un blog opérationnel et démontrer les capacités du framework avec gestion des images et CRUD admin intégré.

